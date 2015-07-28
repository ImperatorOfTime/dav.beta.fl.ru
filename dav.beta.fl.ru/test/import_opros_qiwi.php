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
'Qiwi 1',
'',
B'1111',
TRUE,
TRUE,
'qiwi'
) RETURNING id");
list($opros_id) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('����������� �� �� QIWI ������� � ��������� ��������?', $opros_id, 0, 1, 1, 2)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('����� ������ �� ����������� ����� QIWI �������?', $opros_id, 0, 1, 2, 2)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('������ �� ����������� ��� ������ ����� QIWI ���������?', $opros_id, 0, 1, 3, 2)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('��� ����� �� ����������� ����������� ���������?', $opros_id, 0, 1, 4, 2)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('���� �� � ��� ����������� QIWI �������?', $opros_id, 0, 1, 5, 2)
RETURNING id");
list($question_5) = pg_fetch_row($res);





$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��, ����� ������', $question_1, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���, ������� ��� ��� �����', $question_1, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������', $question_1, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('� ��� ����� QIWI �������?', $question_1, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������� �������', $question_2, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������� ���������� ��������', $question_2, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('� ��� ����� QIWI �������?', $question_2, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������ �� ���������, ��� ������ ������ � QIWI �������!', $question_2, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������ ��������� ���������', $question_3, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������ ��� ��� ���������', $question_3, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('QIWI ������� ������ ��� ����� (� ����������, ��������)', $question_3, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������ �� ���� � QIWI ��������', $question_3, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);




$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���������', $question_4, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����������', $question_4, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������', $question_4, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('�� ���������', $question_4, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����, � � ��������� ��� ��� ��� ��������� �����, ��� � ��� ������', $question_5, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���, � �� ����, ��� �� ����������', $question_5, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���, ��� ������� ��� ������������ ����������� ���������', $question_5, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('� �� ��������� ������������ ����������', $question_5, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



///////////////////////////////////////////////



$res = pg_query(DBConnect(), "INSERT INTO opros (name, descr, flags, is_active, is_multi_page, content) VALUES (
'Qiwi 2',
'',
B'1111',
TRUE,
TRUE,
'qiwi'
) RETURNING id");
list($opros_id) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('����������� �� �� QIWI ��������� � ���������?', $opros_id, 0, 1, 1, 2)
RETURNING id");
list($question_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('��� ����� QIWI?', $opros_id, 0, 1, 2, 2)
RETURNING id");
list($question_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('����� ��� �������� ������������� ����������?', $opros_id, 0, 1, 3, 2)
RETURNING id");
list($question_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('��� ����� ����������� �������?', $opros_id, 0, 1, 4, 2)
RETURNING id");
list($question_4) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('��� �� ������ � QIWI ��������?', $opros_id, 0, 1, 5, 2)
RETURNING id");
list($question_5) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_questions (name, opros_id, max_answer, page_num, num, type) VALUES
('��� ��� ����������� ������ � ���:', $opros_id, 0, 1, 6, 2)
RETURNING id");
list($question_6) = pg_fetch_row($res);




$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��, �������', $question_1, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('� ��� ����� QIWI?', $question_1, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('� ��� ����� ��������� �������?', $question_1, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��, � ���� � �� ����...', $question_1, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���� ��� �����', $question_2, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���������� ������� �����, ������� �������', $question_2, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����������� ������', $question_2, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������� ��������� �����', $question_2, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('�����������', $question_3, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����������� �������', $question_3, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������������ �������', $question_3, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����������� �������', $question_3, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);




$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('�������� �������� ����������� �����', $question_4, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('�������, ��������� �� ����������', $question_4, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����� ��������������� ��������� ������������� ��', $question_4, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('��������� ����� ���� �� ������ ����: \"���� ������ ���?\"', $question_4, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);



$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���: ��������� ���������', $question_5, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('����������� ���� ���', $question_5, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������ �� ����, � ��� ���?', $question_5, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���� ������ ������!', $question_5, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);


$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('���� ���!', $question_6, 1, 1, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_1) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������ � ������', $question_6, 1, 2, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_2) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('������������� ������ ������� � ����������', $question_6, 1, 3, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_3) = pg_fetch_row($res);

$res = pg_query(DBConnect(), "INSERT INTO opros_answers (name, question_id, value, num, is_m_other, move_question_id, orig_answer_id, is_m_block, block_questions, is_m_number, block_answer) VALUES
('QIWI �������!', $question_6, 1, 4, FALSE, NULL, NULL, FALSE, NULL, NULL, NULL)
RETURNING id");
list($answer_4) = pg_fetch_row($res);

pg_query(DBConnect(), "COMMIT");

echo "Done";
