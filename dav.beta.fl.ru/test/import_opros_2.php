<?php

// ������ ��������� � ��������

require_once '../classes/stdf.php';

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM opros");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE opros_id_seq RESTART WITH ".($m['max_id']+1));

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM opros_questions");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE opros_questions_id_seq RESTART WITH ".($m['max_id']+1));

$r = pg_query(DBConnect(), "SELECT MAX(id) AS max_id FROM opros_answers");
$m = pg_fetch_array($r);
pg_query(DBConnect(),"ALTER SEQUENCE opros_answers_id_seq RESTART WITH ".($m['max_id']+1));

pg_query(DBConnect(), "START TRANSACTION");

$res = pg_query(DBConnect(), "INSERT INTO opros (name, descr, flags, is_active, is_multi_page, content) VALUES (
'������� VS ���-����',
'<p>������!</p> <p>�� ��� ����� � ������������ ����� ������ � �������� �� ���-�����. ����������, ������������ � ������. ��� ������ 3-5 ����� ������ �������.</p>',
B'1111',
TRUE,
TRUE,
'fulltime'
) RETURNING id");
list($opros_id) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('�� �������� ������ �� �������� � ��������?', $opros_id, 0, 1, 1, 2)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('������ �� �� ��������� � ����?', $opros_id, 0, 1, 2, 2)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('�����, �� ������ ������, ������������ � ���-����� ����� ������� �������?', $opros_id, 0, 1, 3, 1)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('����� ������������ ������� ������ ����� ���-������?', $opros_id, 0, 1, 4, 1)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('��� ������ �� ����� ����������� ����� ������ � ��������?', $opros_id, 0, 1, 5, 2)
RETURNING id");
list($question_5) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('�� �� ����� �������� � ����, ���� ��:', $opros_id, 0, 1, 6, 2)
RETURNING id");
list($question_6) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��', $question_1, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���', $question_1, 2, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��', $question_2, 3, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���', $question_2, 4, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����� ������ ������ ��, ��� ��������', $question_3, 5, 5, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_5) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������ ����� ����������', $question_3, 6, 6, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_6) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� ����� ������ �������', $question_3, 7, 7, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_7) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��������� ������� ����', $question_3, 8, 8, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_8) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� ������������� ������ � ����, ����� �������� ��� ������', $question_3, 9, 9, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_9) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������ ���������� �������', $question_3, 10, 10, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_10) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����� ������� ������', $question_3, 11, 11, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_11) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� �������', $question_3, 12, 12, TRUE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_12) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������������ � ������ � ����������� � ������', $question_4, 13, 13, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_13) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���������, ������� � ���������', $question_4, 14, 14, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_14) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� ������������� ������ ������', $question_4, 15, 15, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_15) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���� �������� ����', $question_4, 16, 16, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_16) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������������� ������', $question_4, 17, 17, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_17) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���������� �����', $question_4, 18, 18, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_18) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������������ ������', $question_4, 19, 19, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_19) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('� ����� ����� ��������, ��� ����', $question_4, 20, 20, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_20) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� �������', $question_4, 21, 21, TRUE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_21) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��, ��� ���������, ��� �����������', $question_5, 22, 22, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_22) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���, ��� ���� ��� ������� ����������', $question_5, 23, 23, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_23) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� ���������� ����� ������� ��������', $question_6, 24, 24, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_24) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� ��������� ������� �����������', $question_6, 25, 25, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_25) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� ���������� ���������� ������', $question_6, 26, 26, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_26) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��� �������', $question_6, 27, 27, TRUE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_27) = pg_fetch_row($res);

pg_query(DBConnect(), "COMMIT");

echo "Done";