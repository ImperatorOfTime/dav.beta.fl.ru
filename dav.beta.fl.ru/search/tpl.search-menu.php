<ul class="search-tabs" data-ga_role="<?=(is_emp() ? 'Employer' : (get_uid(false) ? 'Freelance' : 'Unauthorized'))?>">
    <li <?= $type=='users'?'class="active"':''?>>
        <a href="/search/?type=users" data-ga_type="performer">
            ����� <span class="b-page__desktop b-page__ipad">�����������</span><div class="b-page__iphone">�����������</div>
        </a>
    </li>
    <li <?= $type=='projects'?'class="active"':''?>>
        <a href="/search/?type=projects" data-ga_type="project">
            ����� <span class="b-page__desktop b-page__ipad">�������</span><div class="b-page__iphone">�������</div>
        </a>
    </li>
    <li <?= $sections?'class="active"':''?>>
        <a href="/search/?type=works" data-ga_type="section">
            ����� �� <span class="b-page__desktop b-page__ipad">�������� �����</span><div class="b-page__iphone">�������� �����</div>
        </a>
    </li>
</ul>