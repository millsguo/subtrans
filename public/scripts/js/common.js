/**
 * 添加任务
 * @param taskType
 * @param targetType
 * @param targetId
 */
function addTask(taskType,targetType,targetId)
{
    $.ajax({
        url:"/task/scan/",
        dataType:"json",
        type:"POST",
        data: {
            target:taskType,
            type:targetType,
            id:targetId
        },
        success:function (json) {
            if (json.code === 0) {
                showToasts({title:"恭喜",body:"扫描任务添加完成",icon:"fa fa-check-circle fa-lg",class:"bg-success"});
            } else {
                showToasts({title:"错误",body:json.msg,icon:"fa-exclamation-circle",class:"bg-warning"});
            }
        },
        error:function (XMLHttpRequest,textStatus,errorThrown) {
            alert(XMLHttpRequest.status);
            alert(XMLHttpRequest.readyState);
            alert(textStatus);
        }
    })
}