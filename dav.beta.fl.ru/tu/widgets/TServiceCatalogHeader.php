<?php
/**
 * Class TServiceCatalogHeader
 *
 * ������ - ���� c ����������
 */
class TServiceCatalogHeader extends CWidget 
{
        public function run() 
        {
            //�������� ������
            $this->render('t-service-catalog-header', array(
                'page_title' => SeoTags::getInstance()->getH1()
            ));
	}
}