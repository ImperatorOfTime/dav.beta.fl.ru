<?php

// ������ ��������� � ��������

require_once '../classes/stdf.php';

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM surveys");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE surveys_id_seq RESTART WITH ".($m['max_id']+1));

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM surveys_questions");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE surveys_questions_id_seq RESTART WITH ".($m['max_id']+1));

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM surveys_questions_options");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE surveys_questions_options_id_seq RESTART WITH ".($m['max_id']+1));

pg_query(DBConnect(), "START TRANSACTION");

$res = pg_query(DBConnect(), "INSERT INTO surveys (title, description, date_begin, date_end, code, visibility, thanks_text, u_count, e_count, f_count) VALUES (
'������� ������� �� ��������� � ���������� �����?',
'������!<br/>�� ����� ����� ��� ��� ��� ����� ������. ������� ����� ������ ��� ������������� � ��������� ������, ����������� ���������� �����. �� ������ � ��� ����� ���� �����, � �� ������, ����� ���������� ����� �� ������� ������������ � ������. �������!',
'2011-06-22 00:00:00',
'2011-07-01 00:00:00',
'',
1,
'���������� ����� ������� ��� ����� ������ ���. �������, ��� ������� ��� ����� � �������� �� ��� �������!',
0,
0,
0
) RETURNING id");
list($opros_id) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('���� �� � ��� �������� � ���������� �����?', '', 2, 't', 1, 1, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('� ����� ���������� ���� �� ������� �������� �����?', '', 2, 't', 1, 2, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('��� ����� �� �������� � ���������� ����?', '', 2, 't', 1, 3, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('������� ������� � ����� ��������� �� ��������� � ���������� ���� � ����?', '', 2, 't', 1, 4, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('��������� �� ��� ���������� ���� �� ������?', '', 2, 't', 1, 5, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_5) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('�������� �� �� � ���������� ���� �� ����� ������ ��� ��������?', '', 2, 't', 1, 6, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_6) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions (title, description, type, is_visible, page, num, survey_id, max_answers, is_number, number_min, number_max) VALUES
('�������� �� �� ����������/������������ � ���������� �����?', '', 2, 't', 1, 7, {$opros_id}, 0, 't', NULL, NULL)
RETURNING id");
list($question_7) = pg_fetch_row($res);




//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('��', 'f', 'f', 0, {$question_1}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_11) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('���', 'f', 'f', 0, {$question_1}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_12) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('Facebook', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_21) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('���������', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_22) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('��� ���', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 3, 0, 0, 0)
RETURNING id");
list($answer_23) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('�������������', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 4, 0, 0, 0)
RETURNING id");
list($answer_24) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('Twitter', 'f', 'f', 0, {$question_2}, 'f', NULL, NULL, 5, 0, 0, 0)
RETURNING id");
list($answer_25) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('���� ������ ���� � ����', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_31) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('1-3 ���� � ����', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_32) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('3-7 ��� � ����', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 3, 0, 0, 0)
RETURNING id");
list($answer_33) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('7-15 ��� � ����', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 4, 0, 0, 0)
RETURNING id");
list($answer_34) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('����� 15 ��� � ����', 'f', 'f', 0, {$question_3}, 'f', NULL, NULL, 5, 0, 0, 0)
RETURNING id");
list($answer_35) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('����� ����', 'f', 'f', 0, {$question_4}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_41) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('1-3 ����', 'f', 'f', 0, {$question_4}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_42) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('3-5 �����', 'f', 'f', 0, {$question_4}, 'f', NULL, NULL, 3, 0, 0, 0)
RETURNING id");
list($answer_43) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('����� 5 �����', 'f', 'f', 0, {$question_4}, 'f', NULL, NULL, 4, 0, 0, 0)
RETURNING id");
list($answer_44) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('��', 'f', 'f', 0, {$question_5}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_51) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('���', 'f', 'f', 0, {$question_5}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_52) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('�����', 'f', 'f', 0, {$question_6}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_61) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('������', 'f', 'f', 0, {$question_6}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_62) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('�������', 'f', 'f', 0, {$question_6}, 'f', NULL, NULL, 3, 0, 0, 0)
RETURNING id");
list($answer_63) = pg_fetch_row($res);

//--

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('��', 'f', 'f', 0, {$question_7}, 'f', NULL, NULL, 1, 0, 0, 0)
RETURNING id");
list($answer_71) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO surveys_questions_options (title, is_other, is_block, value, question_id, is_number, number_min, number_max, num, u_count, e_count, f_count) VALUES
('���', 'f', 'f', 0, {$question_7}, 'f', NULL, NULL, 2, 0, 0, 0)
RETURNING id");
list($answer_72) = pg_fetch_row($res);


pg_query(DBConnect(), "COMMIT");

echo "Done";
