<?php

/**
 * ����� ��� ������ � ������������� � ��������� �������
 */
require_once 'Comments.php';

class CommentsAdminLog extends TComments {
    
    public $enableRating = false;
    
    /**
     * ������ ������ �������� � �������������
     * 
     * @var string
     */
    public $urlTemplate = '';
    
    /**
     * ���������� ����������� �� �������� �����������.
     * � ����������� ������������ urlTemplate
     * 
     * @var bool
     */
    public $sendDeleteWarn = false;
    
    /**
     * ������ ������ ��� ������������ �������.
     * 
     * @return array
     */
    public function model() {
        return array(
            // �����������
            'comments' => array(
                'table'  => 'admin_log_comments',
                'fields' => array(
                    'id'            => 'id',
                    'resource'      => 'log_id',
                    'author'        => 'from_id',
                    'parent_id'     => 'reply_to',
                    'msgtext'       => 'msgtext',
                    'yt'            => 'yt_link',
                    'created_time'  => 'post_time',
                    'modified'      => 'modified_id',
                    'modified_time' => 'modified',
                    'deleted'       => 'deluser_id',
                    'deleted_time'  => 'deleted',
                    'rating'        => null,
                )
            ),
            // �����, ���� ������ � ��������� �������
            'attaches' => array(
                'file_table' => 'file',
                'table'      => 'admin_log_comments_files',
                'fields'     => array(
                    'comment' => 'comment_id',
                    'file'    => 'file_id',
                )
            )
        );
    }
}
