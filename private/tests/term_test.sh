#!/usr/bin/env bash
# ============================================================
# Full-term regression test — Uaddara SBA
#
# Plays one complete school term (admin setup → subject teachers →
# form teacher → publish → promotion → new year) against a THROWAWAY
# copy of the database, and prints PASS/FAIL for every data-safety rule.
#
# Usage (from the project root, in Git Bash, with XAMPP MySQL running):
#     bash private/tests/term_test.sh
#
# Safe: the real database is only read (mysqldump). All writes go to
# uaddara_sba_test, which is dropped at the end (pass --keep to inspect it).
# ============================================================
set -u
export MSYS_NO_PATHCONV=1

ROOT="$(cd "$(dirname "$0")/../.." && pwd)"
command -v cygpath > /dev/null && ROOT="$(cygpath -m "$ROOT")"   # Windows PHP needs C:/… paths
HERE="$ROOT/private/tests"
OUT="$HERE/output"
PHP=${PHP:-/c/xampp/php/php.exe}
MYSQL=${MYSQL:-/c/xampp/mysql/bin/mysql.exe}
MYSQLDUMP=${MYSQLDUMP:-/c/xampp/mysql/bin/mysqldump.exe}
REAL_DB=uaddara_sba
TEST_DB=uaddara_sba_test
PORT=${PORT:-8765}
BASE="http://127.0.0.1:$PORT"
PASSWORD='Test@1234'
KEEP=0; [ "${1:-}" = "--keep" ] && KEEP=1

mkdir -p "$OUT"; rm -f "$OUT"/*.jar "$OUT"/*.html; : > "$OUT/php_errors.log"
PASS=0; FAIL=0

q()  { "$MYSQL" -uroot -N -B "$TEST_DB" -e "$1"; }
ok() { if [ "$2" = "1" ]; then PASS=$((PASS+1)); echo "  PASS  $1"; else FAIL=$((FAIL+1)); echo "  FAIL  $1${3:+   ($3)}"; fi; }
is() { [ "$2" = "$3" ] && ok "$1" 1 || ok "$1" 0 "expected '$3', got '$2'"; }

# ── HTTP helpers ─────────────────────────────────────────────
declare -A TOK
login() {  # login <user> <email>
  rm -f "$OUT/$1.jar"
  local t; t=$(curl -s -c "$OUT/$1.jar" "$BASE/login" | grep -o 'name="_csrf_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
  curl -s -L -b "$OUT/$1.jar" -c "$OUT/$1.jar" --data-urlencode "_csrf_token=$t" -d role_type=staff \
       --data-urlencode "email=$2" --data-urlencode "password=$PASSWORD" "$BASE/login" > /dev/null
  TOK[$1]=$(curl -s -b "$OUT/$1.jar" -c "$OUT/$1.jar" "$BASE/profile/password" | grep -o 'name="_csrf_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
}
get()  { curl -s -b "$OUT/$1.jar" -c "$OUT/$1.jar" "$BASE$2"; }
code() { curl -s -o /dev/null -w '%{http_code}' -b "$OUT/$1.jar" -c "$OUT/$1.jar" "$BASE$2"; }
form() {  # form <user> <path> k=v ...   (follows redirect; page saved to last.html)
  local u=$1 p=$2; shift 2; local a=(); for kv in "$@"; do a+=(--data-urlencode "$kv"); done
  curl -s -L -b "$OUT/$u.jar" -c "$OUT/$u.jar" --data-urlencode "_csrf_token=${TOK[$u]}" "${a[@]}" "$BASE$p" > "$OUT/last.html"
}
ajax() {  # ajax <user> <path> k=v ...   (prints raw response)
  local u=$1 p=$2; shift 2; local a=(); for kv in "$@"; do a+=(--data-urlencode "$kv"); done
  curl -s -b "$OUT/$u.jar" -c "$OUT/$u.jar" -H 'X-Requested-With: XMLHttpRequest' --data-urlencode "_csrf_token=${TOK[$u]}" "${a[@]}" "$BASE$p"
}
flashed() { grep -q "alert alert-$1" "$OUT/last.html" && echo 1 || echo 0; }
succeeded() { case "$1" in *'"success":true'*) echo 1;; *) echo 0;; esac; }
student_login() {  # student_login <jar> <student_id_number>
  rm -f "$OUT/$1.jar"
  local t; t=$(curl -s -c "$OUT/$1.jar" "$BASE/login" | grep -o 'name="_csrf_token" value="[^"]*"' | head -1 | sed 's/.*value="//;s/"//')
  curl -s -L -b "$OUT/$1.jar" -c "$OUT/$1.jar" --data-urlencode "_csrf_token=$t" -d role_type=student -d "student_id=$2" -d pin=1234 "$BASE/login" > /dev/null
}

# ── 1. Fresh copy of the database ───────────────────────────
echo "Preparing $TEST_DB from $REAL_DB (read-only dump)…"
"$MYSQL" -uroot -e "DROP DATABASE IF EXISTS $TEST_DB; CREATE DATABASE $TEST_DB CHARACTER SET utf8mb4;" || exit 1
"$MYSQLDUMP" -uroot "$REAL_DB" | "$MYSQL" -uroot "$TEST_DB" || exit 1
for m in "$ROOT"/private/database/create_assessments.sql "$ROOT"/private/database/fix_data_integrity.sql "$ROOT"/private/database/sms_delivery_tracking.sql; do
  [ -f "$m" ] && { "$MYSQL" -uroot "$TEST_DB" < "$m" || echo "  (migration $(basename "$m") reported errors)"; }
done

# Test accounts (only in the copy)
HASH=$("$PHP" -r "echo password_hash('$PASSWORD', PASSWORD_BCRYPT);")
ADMIN_EMAIL=$(q "SELECT email FROM users WHERE role='admin' AND is_active=1 ORDER BY id LIMIT 1")
read -r T1 T2 T3 <<< "$(q "SELECT GROUP_CONCAT(id SEPARATOR ' ') FROM (SELECT id FROM users WHERE role='teacher' AND is_active=1 AND email IS NOT NULL ORDER BY id LIMIT 3) t")"
q "UPDATE users SET password_hash='$HASH' WHERE email='$ADMIN_EMAIL' OR id IN ($T1,$T2,$T3); DELETE FROM login_attempts;"
E1=$(q "SELECT email FROM users WHERE id=$T1"); E2=$(q "SELECT email FROM users WHERE id=$T2"); E3=$(q "SELECT email FROM users WHERE id=$T3")

# ── 2. Test server (refuses to run against anything but the copy) ──
if curl -s -m 2 -o /dev/null "$BASE/"; then
  echo "ABORT: port $PORT is already in use by another program. Re-run with PORT=<free port>."; exit 1
fi
"$PHP" -S 127.0.0.1:$PORT -t "$ROOT" "$HERE/router.php" > "$OUT/server.log" 2>&1 &
SERVER=$!
trap 'kill $SERVER 2>/dev/null; [ $KEEP = 0 ] && "$MYSQL" -uroot -e "DROP DATABASE IF EXISTS '"$TEST_DB"'"' EXIT
for _ in $(seq 40); do curl -s "$BASE/__whichdb" > /dev/null && break; sleep 0.25; done
[ "$(curl -s "$BASE/__whichdb")" = "$TEST_DB" ] || { echo "ABORT: test server is not using $TEST_DB"; exit 1; }

login admin "$ADMIN_EMAIL"; login subj "$E1"; login subj2 "$E2"; login form "$E3"

JHS=$(q "SELECT id FROM school_levels WHERE code='JHS'")
subj() { q "SELECT id FROM subjects WHERE level_id=$JHS AND subject_name='$1' LIMIT 1"; }
S_ENG=$(subj 'English Language'); S_MATH=$(subj 'Mathematics'); S_SCI=$(subj 'Integrated Science'); S_SOC=$(subj 'Social Studies')
S_EL1=$(q "SELECT id FROM subjects WHERE level_id=$JHS AND subject_name NOT IN ('English Language','Mathematics','Integrated Science','Social Studies') ORDER BY sort_order LIMIT 1")
S_LOWER=$(q "SELECT id FROM subjects WHERE level_id<>$JHS AND subject_name='English Language' LIMIT 1")

# ════════════════════════════════════════════════════════════
echo; echo "ADMIN — set up the term"
form admin /admin/years _action=year_store year_name=2098/2099
Y1=$(q "SELECT id FROM academic_years WHERE year_name='2098/2099'")
form admin /admin/years _action=term_store academic_year_id=$Y1 term_number=1 name="Term 1" total_school_days=60 start_date=2098-09-08 end_date=2098-12-18 next_term_begins=2099-01-06
TM=$(q "SELECT id FROM terms WHERE academic_year_id=$Y1")
form admin /admin/years _action=term_activate term_id=$TM

form admin /admin/years _action=term_store academic_year_id=$Y1 term_number=1 name="Term 1" total_school_days=60 start_date= end_date= next_term_begins=
ok "Duplicate term shows an error, not a blank page" "$(flashed danger)"

form admin /admin/classes _action=class_store class_name="BASIC 8" section=A level_id=$JHS academic_year_id=$Y1 grading_system=proficiency
form admin /admin/classes _action=class_store class_name="BASIC 8" section=B level_id=$JHS academic_year_id=$Y1 grading_system=proficiency
form admin /admin/classes _action=class_store class_name="BASIC 9" section=A level_id=$JHS academic_year_id=$Y1 grading_system=waec "teacher_ids[]=$T3"
C8A=$(q "SELECT id FROM classes WHERE academic_year_id=$Y1 AND class_name='BASIC 8' AND section='A'")
C8B=$(q "SELECT id FROM classes WHERE academic_year_id=$Y1 AND class_name='BASIC 8' AND section='B'")
C9A=$(q "SELECT id FROM classes WHERE academic_year_id=$Y1 AND class_name='BASIC 9' AND section='A'")
OTHER_YEAR_CLASS=$(q "SELECT id FROM classes WHERE academic_year_id<>$Y1 ORDER BY id LIMIT 1")

reg() { form admin /admin/students _action=student_store full_name="$1" surname="$2" gender="$3" date_of_birth=2011-01-01 current_class_id=$4 academic_year_id=$Y1; }
for n in Kofi Ama Yaw Abena Kwabena; do reg "$n Test9" Test9 Male $C9A; done
for n in Esi Kojo; do reg "$n Test8A" Test8A Female $C8A; done
for n in Akua Fiifi; do reg "$n Test8B" Test8B Male $C8B; done
reg "Wrong Year" Wrongyear Female $OTHER_YEAR_CLASS
CLASS_YEAR=$(q "SELECT academic_year_id FROM classes WHERE id=$OTHER_YEAR_CLASS")
STU_YEAR=$(q "SELECT academic_year_id FROM students WHERE surname='Wrongyear' ORDER BY id DESC LIMIT 1")
is "Student's year always matches the class's year" "$STU_YEAR" "$CLASS_YEAR"
form admin /admin/students _action=student_store full_name="No Gender" surname=Nogender gender= date_of_birth= current_class_id=$C9A academic_year_id=$Y1
is "Blank gender is stored as empty (NULL), not ''" "$(q "SELECT IFNULL(gender,'NULL') FROM students WHERE surname='Nogender' ORDER BY id DESC LIMIT 1")" "NULL"
q "UPDATE students SET status='inactive' WHERE surname IN ('Nogender','Wrongyear')"   # keep later counts simple

read -r K AM Y AB KW <<< "$(q "SELECT GROUP_CONCAT(id ORDER BY id SEPARATOR ' ') FROM students WHERE surname='Test9'")"
read -r E8 KJ <<< "$(q "SELECT GROUP_CONCAT(id ORDER BY id SEPARATOR ' ') FROM students WHERE surname='Test8A'")"
read -r AK FI <<< "$(q "SELECT GROUP_CONCAT(id ORDER BY id SEPARATOR ' ') FROM students WHERE surname='Test8B'")"

form admin /admin/teachers _action=assign_subject teacher_id=$T1 "class_ids[]=$C9A" "class_ids[]=$C8A" "class_ids[]=$C8B" "subject_ids[]=$S_ENG" "subject_ids[]=$S_MATH"
form admin /admin/teachers _action=assign_subject teacher_id=$T2 "class_ids[]=$C9A" "subject_ids[]=$S_SCI" "subject_ids[]=$S_SOC" "subject_ids[]=$S_EL1"
form admin /admin/teachers _action=assign_subject teacher_id=$T2 "class_ids[]=$C9A" "subject_ids[]=$S_LOWER"
is "A class cannot get two subjects with the same name" "$(q "SELECT COUNT(*) FROM class_subjects WHERE class_id=$C9A AND subject_id=$S_LOWER")" "0"

cs() { q "SELECT id FROM class_subjects WHERE class_id=$1 AND subject_id=$2 AND term_id=$TM"; }
ENG9=$(cs $C9A $S_ENG); MATH9=$(cs $C9A $S_MATH); SCI9=$(cs $C9A $S_SCI); SOC9=$(cs $C9A $S_SOC); EL9=$(cs $C9A $S_EL1)
ENG8A=$(cs $C8A $S_ENG); ENG8B=$(cs $C8B $S_ENG)

# ════════════════════════════════════════════════════════════
echo; echo "SUBJECT TEACHERS — term scores"
put() { ajax $1 /teacher/scores student_id=$2 class_subject_id=$3 term_id=$TM field=$4 value="$5"; }
i=0
for st in $K $AM $Y $AB $KW; do i=$((i+1))
  for c in $ENG9 $MATH9; do for f in class_test group_work project individual_test; do put subj $st $c $f $(( (i*3+c) % 6 + 9 )) >/dev/null; done; put subj $st $c raw_score $(( (i*17+c) % 50 + 45 )) >/dev/null; done
  for c in $SCI9 $SOC9 $EL9; do for f in class_test group_work project individual_test; do put subj2 $st $c $f $(( (i*5+c) % 7 + 8 )) >/dev/null; done; put subj2 $st $c raw_score $(( (i*13+c) % 55 + 35 )) >/dev/null; done
done
for st in $E8 $KJ; do put subj $st $ENG8A class_test 12 >/dev/null; put subj $st $ENG8A raw_score 70 >/dev/null; done
for st in $AK $FI; do put subj $st $ENG8B class_test 11 >/dev/null; put subj $st $ENG8B raw_score 65 >/dev/null; done

BEFORE=$(q "SELECT raw_score FROM exam_scores WHERE student_id=$K AND class_subject_id=$ENG9")
is "Typing 'abc' as a score is rejected" "$(succeeded "$(put subj $K $ENG9 raw_score abc)")" "0"
is "…and the existing score is unchanged" "$(q "SELECT raw_score FROM exam_scores WHERE student_id=$K AND class_subject_id=$ENG9")" "$BEFORE"
is "Out-of-range component (16/15) is rejected" "$(succeeded "$(put subj $K $ENG9 class_test 16)")" "0"
ajax subj /teacher/scores student_id=$AM class_subject_id=$ENG9 term_id=1 field=raw_score value=77 > /dev/null
is "Score is saved under the subject's own term, not the browser's" "$(q "SELECT COUNT(*) FROM exam_scores WHERE student_id=$AM AND class_subject_id=$ENG9 AND term_id<>$TM")" "0"
is "Score for a student outside the class is rejected" "$(succeeded "$(put subj $E8 $ENG9 raw_score 90)")" "0"
is "Teacher cannot save into another teacher's subject" "$(succeeded "$(put subj2 $K $ENG9 raw_score 90)")" "0"
put subj $Y $ENG9 project 12.5 > /dev/null
get subj "/teacher/scores?id=$ENG9" > "$OUT/grid.html"
is "Decimal score shows as stored (12.5, not 12)" "$(grep -c 'value="12.5"' "$OUT/grid.html")" "1"

echo; echo "SUBJECT TEACHERS — Mock 1"
form admin /admin/assessments _action=assessment_store term_id=$TM assessment_type=mock title="Mock 1" max_score=50
MOCK=$(q "SELECT id FROM assessments WHERE term_id=$TM AND title='Mock 1'")
mk() { ajax $1 /teacher/assessments assessment_id=$MOCK class_subject_id=$3 student_id=$2 value="$4"; }
for st in $K $AM $Y $AB $KW; do for c in $ENG9 $MATH9; do mk subj $st $c 35 >/dev/null; done; for c in $SCI9 $SOC9 $EL9; do mk subj2 $st $c 30 >/dev/null; done; done
is "Mock: 'abc' is rejected" "$(succeeded "$(mk subj $K $ENG9 abc)")" "0"
is "Mock: grade computed (35/50 = 70% → WAEC 2)" "$(q "SELECT grade FROM assessment_scores WHERE assessment_id=$MOCK AND student_id=$K AND class_subject_id=$ENG9")" "2"

# ════════════════════════════════════════════════════════════
echo; echo "FORM TEACHER — attendance, conduct, remarks"
ft() { ajax ${4:-form} "/teacher/class?id=$C9A" student_id=$1 field=$2 value="$3"; }
for st in $K $AM $Y $AB $KW; do ft $st days_present 55 >/dev/null; ft $st conduct_character 4 >/dev/null; ft $st attitude 3 >/dev/null; ft $st teacher_remark "Hardworking and respectful." >/dev/null; done
is "Attendance above term days (75/60) is rejected" "$(succeeded "$(ft $K days_present 75)")" "0"
is "Negative attendance is rejected" "$(succeeded "$(ft $K days_present -3)")" "0"
is "Conduct rating 9 (scale 1-5) is rejected" "$(succeeded "$(ft $K attitude 9)")" "0"
ft $AM conduct_character "" > /dev/null
is "Clearing a rating stores NULL, not 0" "$(q "SELECT IFNULL(conduct_character,'NULL') FROM student_remarks WHERE student_id=$AM AND term_id=$TM")" "NULL"
is "Form teacher cannot edit a child in another class" "$(succeeded "$(ft $E8 teacher_remark 'not my class')")" "0"
R=$(ft 999999 teacher_remark x)
case "$R" in *SQLSTATE*) ok "No raw database error shown to teachers" 0 "$R";; *) ok "No raw database error shown to teachers" 1;; esac

echo; echo "ADMIN — headmaster remarks, lock, publish"
get admin "/admin/remarks?class_id=$C9A" > "$OUT/remarks.html"
is "Remarks class list shows each class once (current year only)" "$(grep -c '<option value="'"$C9A"'"' "$OUT/remarks.html")" "1"
DUPES=$(tr '\n' ' ' < "$OUT/remarks.html" | grep -oE '<option value="[0-9]+"[^>]*>[^<]*' | sed -E 's/<option value="[0-9]+"[^>]*>//; s/\s+/ /g' | sort | uniq -d | wc -l)
is "No duplicate class names in the remarks list" "$DUPES" "0"
for st in $K $AM $Y $AB $KW; do ajax admin "/admin/remarks?class_id=$C9A" student_id=$st field=headmaster_remark value="Aim higher." >/dev/null; done

for c in $C9A $C8A $C8B; do form admin /admin/publish _action=lock_class class_id=$c term_id=$TM; form admin /admin/publish _action=publish_class class_id=$c term_id=$TM send_sms=0; done
is "Publishing works" "$(q "SELECT COUNT(*) FROM report_card_locks WHERE term_id=$TM AND is_published=1")" "3"
is "Locked: teacher cannot change scores" "$(succeeded "$(put subj $K $ENG9 raw_score 99)")" "0"
is "Published: form teacher cannot change remarks" "$(succeeded "$(ft $K teacher_remark 'changed after publish')")" "0"
is "Published: form teacher cannot change attendance" "$(succeeded "$(ft $K days_present 40)")" "0"
is "Published: admin cannot change headmaster remark" "$(succeeded "$(ajax admin "/admin/remarks?class_id=$C9A" student_id=$K field=headmaster_remark value=changed)")" "0"
form admin /admin/publish _action=unlock_class class_id=$C8B term_id=$TM
ok "Unlock shows a confirmation message" "$(flashed info)"
form admin /admin/publish _action=lock_class class_id=$C8B term_id=$TM; form admin /admin/publish _action=publish_class class_id=$C8B term_id=$TM send_sms=0

POS=$(q "SELECT GROUP_CONCAT(IFNULL(aggregate_grade,99) ORDER BY class_position, student_id) FROM student_aggregates WHERE class_id=$C9A AND term_id=$TM")
SORTED=$(echo "$POS" | tr ',' '\n' | sort -n | paste -sd, -)
is "WAEC class: positions follow the aggregate (best aggregate = 1st)" "$POS" "$SORTED"
BLOCKED=$(code subj2 "/report?student=$E8&term=$TM"); case $BLOCKED in 403|404) BLOCKED=blocked;; esac
is "Subject teacher who doesn't teach the class cannot open its reports" "$BLOCKED" "blocked"
is "Subject teacher of the class can open its report" "$(code subj "/report?student=$E8&term=$TM")" "200"

# ════════════════════════════════════════════════════════════
echo; echo "END OF YEAR — promotion and year transition"
form admin /admin/years _action=year_store year_name=2099/2100 clone_classes=1 clone_source_year_id=$Y1
Y2=$(q "SELECT id FROM academic_years WHERE year_name='2099/2100'")
form admin /admin/years _action=term_store academic_year_id=$Y2 term_number=1 name="Term 1" total_school_days=65 start_date=2099-09-08 end_date=2099-12-18 next_term_begins=2100-01-06
TN=$(q "SELECT id FROM terms WHERE academic_year_id=$Y2")
form admin /admin/promotions _action=auto_promote class_id=$C8A year_id=$Y1 next_year_id=$Y2 target_class_id="auto:BASIC 9" threshold=0
form admin /admin/promotions _action=auto_promote class_id=$C8B year_id=$Y1 next_year_id=$Y2 target_class_id="auto:BASIC 9" threshold=0
form admin /admin/promotions _action=auto_promote class_id=$C9A year_id=$Y1 next_year_id=$Y2 threshold=0
form admin /admin/transition _action=year_transition source_year_id=$Y1 target_year_id=$Y2 target_term_id=$TN

N9A=$(q "SELECT id FROM classes WHERE academic_year_id=$Y2 AND class_name='BASIC 9' AND section='A'")
N9B=$(q "SELECT id FROM classes WHERE academic_year_id=$Y2 AND class_name='BASIC 9' AND section='B'")
is "BASIC 8 A children are in BASIC 9 A" "$(q "SELECT COUNT(*) FROM students WHERE id IN ($E8,$KJ) AND current_class_id=$N9A")" "2"
is "BASIC 8 B children are in BASIC 9 B (sections not merged)" "$(q "SELECT COUNT(*) FROM students WHERE id IN ($AK,$FI) AND current_class_id=$N9B")" "2"
is "New year's BASIC 9 A kept WAEC grading" "$(q "SELECT grading_system FROM classes WHERE id=$N9A")" "waec"
is "Graduates are marked 'graduated'" "$(q "SELECT COUNT(*) FROM students WHERE id IN ($K,$AM,$Y,$AB,$KW) AND status='graduated'")" "5"
is "Admin can still open a graduate's final report" "$(code admin "/report?student=$K&term=$TM")" "200"
student_login kid "$(q "SELECT student_id_number FROM students WHERE id=$E8")"
is "Promoted child can still open last term's report" "$(code kid "/report?student=$E8&term=$TM")" "200"
get admin "/report?student=$AK&term=$TM" > "$OUT/oldreport.html"
is "Old report shows the class the child was in that term" "$(grep -c 'BASIC 8' "$OUT/oldreport.html" | awk '{print ($1>0)?1:0}')" "1"
get subj "/teacher/assessments?term=$TM" > "$OUT/oldmocks.html"
is "Teacher can still view last term's mocks" "$(grep -c 'Mock 1' "$OUT/oldmocks.html" | awk '{print ($1>0)?1:0}')" "1"

echo; echo "SMS DELIVERY TRACKING (fake log rows only — no SMS is sent)"
q "INSERT INTO sms_logs (recipient_phone, message, sms_type, status) VALUES ('0200000001','old row','broadcast','sent');
   INSERT INTO sms_logs (recipient_phone, message, sms_type, status, status_code) VALUES ('0200000002','rejected row','broadcast','rejected',2128);
   INSERT INTO sms_logs (recipient_phone, message, sms_type, status, batch_id) VALUES ('0200000003','waiting row','broadcast','pending','test-not-a-real-batch');"
SMSID=$(q "SELECT id FROM sms_logs WHERE batch_id='test-not-a-real-batch'")
get admin /admin/sms > "$OUT/sms.html"
is "SMS history says why a message was rejected" "$(grep -c 'Sender ID is not registered' "$OUT/sms.html" | awk '{print ($1>0)?1:0}')" "1"
is "Old untracked SMS are not shown as delivered" "$(grep -c 'NOT TRACKED' "$OUT/sms.html" | awk '{print ($1>0)?1:0}')" "1"
form admin /admin/sms _action=check_delivery
is "Check delivery runs and reports back" "$(grep -c 'Checked [0-9]* message' "$OUT/last.html" | awk '{print ($1>0)?1:0}')" "1"
is "A checked message is stamped so the next click moves on" "$(q "SELECT status_checked_at IS NOT NULL FROM sms_logs WHERE id=$SMSID")" "1"

echo; echo "EVERY PAGE OPENS (admin, subject teacher, form teacher)"
BAD=""
for p in /admin /admin/years /admin/transition /admin/classes /admin/subjects /admin/teachers /admin/students \
         /admin/import /admin/export /admin/publish /admin/settings /admin/sms /admin/promotions /admin/remarks \
         /admin/assessments "/admin/publish/print?class_id=$C9A&term_id=$TM" "/assessments/results?assessment=$MOCK&class=$C9A" \
         /teacher /teacher/scores "/teacher/scores?id=$ENG9" /teacher/import /teacher/reports /teacher/assessments \
         "/report?student=$K&term=$TM" /profile/password; do
  c=$(code admin "$p"); case $c in 200|302) ;; *) BAD="$BAD admin:$p=$c";; esac
done
for u in subj form; do
  for p in /teacher /teacher/scores /teacher/import /teacher/reports /teacher/assessments /admin/promotions /profile/password "/teacher/class?id=$N9A"; do
    c=$(code $u "$p"); case $c in 200|302) ;; *) BAD="$BAD $u:$p=$c";; esac
  done
done
is "All pages load without server errors" "${BAD:-none}" "none"

echo; echo "SAFETY NETS"
is "Score and remark changes are recorded in the audit log" "$(q "SELECT COUNT(DISTINCT action) >= 2 FROM audit_log WHERE action IN ('score_update','remark_update','attendance_update')")" "1"
is "Database blocks an impossible rating even if code fails" "$("$MYSQL" -uroot "$TEST_DB" -e "UPDATE student_remarks SET attitude=9 WHERE student_id=$K" 2>&1 | grep -c -i 'constraint')" "1"

echo; echo "PHP errors/warnings during the run:"
ERRS=$(sed 's/^\[[^]]*\] //' "$OUT/php_errors.log" | grep -v "DB_NAME already defined" | grep -E "PHP (Fatal|Warning|Notice|Deprecated)" | sort | uniq -c)
if [ -z "$ERRS" ]; then ok "No PHP errors or warnings" 1; else echo "$ERRS"; ok "No PHP errors or warnings" 0; fi

echo; echo "RESULT: $PASS passed, $FAIL failed"
[ $KEEP = 1 ] && echo "(kept $TEST_DB for inspection)"
[ $FAIL = 0 ]
