function ProjectsStatus()
{
    ProjectsStatus=this; // ie ������� ��� �����, ���� �� �����.
    
    
    //--------------------------------------------------------------------------
    
    //��������� �������������
    this.init = function() 
    {
    };
    
    //--------------------------------------------------------------------------
    
    
    this.changeStatus = function(project_id, status, hash)
    {      
        //@todo: ������-�� xajax ��������� � double?
        var param = {
            project_id:project_id.toString(),  
            status:status,
            hash:hash
        };

        xajax_changeProjectStatus(param);
    };
    
   
    //--------------------------------------------------------------------------
    
    
    //������ �������������
    this.init();    
}

window.addEvent('domready', function() {
    new ProjectsStatus();
});