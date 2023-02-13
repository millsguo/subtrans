/**
 * Toasts 显示配置
 * @param msgArray [
 *  title:'标题',
 *  subtitle:'副标题',
 *  body:'内容',
 *  position:'topRight|topLeft|bottomRight|bottomLeft',
 *  autohide:true,
 *  delay:750,
 *  fixed:true,
 *  icon:'fa fa-envelope fa-lg',
 *  image:'https://.../img.png',
 *  imageAlt:''
 *  class:'bg-success'
 *  ]
 */
function showToasts(msgArray){
    $(document).Toasts('create', msgArray);
}

/**
 * 定义SweetAlert2对象
 * @type SweetAlert
 */
var toastsAlertObj = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000
});

/**
 * 配置SweetAlert2
 * @param configArray
 */
function configAlert(configArray) {
    toastsAlertObj = Swal.mixin(configArray);
}

/**
 * 显示SweetAlert2提醒消息
 * @param msgType
 * @param msgTitle
 */
function showAlert(msgType,msgTitle){
    toastsAlertObj.fire({
        type:msgTitle,
        title:msgTitle
    });
}
/**
 * 显示Toastr类型消息
 * @param msgType
 * @param msgTitle
 */
function showToastr(msgType,msgTitle) {
    switch (msgType) {
        default:
            toastr.success(msgTitle);
            break;
        case 'info':
            toastr.info(msgTitle);
            break;
        case 'warning':
            toastr.warning(msgTitle);
            break;
        case 'error':
        case 'danger':
            toastr.error(msgTitle);
            break;
    }
}

/**
 * AJAX载入页面
 * @param url
 * @param targetObj
 */
function showPage(url,targetObj)
{
    $.ajax({
        method:"GET",
        url:url
    })
    .done(function(html){
        $(targetObj).innerHTML = "";
        $(targetObj).html(html);
    });
}

/**
 * AJAX打开MODAL
 * @param url
 */
function showModal(url)
{
    let myModal = $('#show-modal');
    let showModal = $('#show-modal .modal-content');
    myModal.removeData('bs.modal');
    showModal.children().remove();
    showModal.load(url);
    /*Bootstrap 4已取消remote支持
    myModal.modal({remote: url });
     */
    myModal.modal('show');
}

/**
 * 隐藏MODAL
 */
function hiddenModal()
{
    let myModal = $('#show-modal');
    myModal.modal('hide');
}

$("#show-modal").on("hidden.bs.modal",function(e){
    let showModal = $('#show-modal .modal-content');
    showModal.html('<div class="overlay d-flex justify-content-center align-items-center">\n' +
        '                    <i class="fas fa-2x fa-sync fa-spin"></i>\n' +
        '                </div>\n' +
        '                <div class="modal-header">\n' +
        '                    <h4 class="modal-title">正在载入</h4>\n' +
        '                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">\n' +
        '                        <span aria-hidden="true">×</span>\n' +
        '                    </button>\n' +
        '                </div>\n' +
        '                <div class="modal-body">\n' +
        '                    <p>数据正在加载，请稍候……</p>\n' +
        '                </div>\n' +
        '                <div class="modal-footer justify-content-between">\n' +
        '                    <button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>\n' +
        '                </div>');
});

var submitFormFlag = false;
function submitForm(formTag,submitBtn)
{
    if (submitFormFlag)
    {
        return false;
    }
    else
    {
        submitFormFlag = true;
    }
    $(window).unbind('beforeunload');
    $("#"+submitBtn).val("正在提交");
    $("#"+submitBtn).html("正在提交");
    $("#"+submitBtn).attr("disabled","true");
    $("#"+formTag).submit();
}


function selectAll(allObjSelecter)
{
    $(allObjSelecter).each(function() {
        $(this).prop("checked", true);
    });
}

function unSelectAll(allObjSelecter)
{
    $(allObjSelecter).each(function() {
        $(this).prop("checked", false);
    });
}

/**
 * 根据目标值自动显示或隐藏
 * @param target
 * @param targetArray
 */
function checkRate(target,targetArray)
{
    $.each(targetArray, function(targetLayer,targetValue){
        let value = $("#"+target).val();

        if (value == targetValue){
            $("#"+targetLayer).show();
        }else{
            $("#"+targetLayer).hide();
        }
    });
}

function setTargetBind(target,targetArray)
{
    $("#"+target).bind("change",function(){
        checkRate(target,targetArray);
    });
}

function setInput(target,targetArray)
{
    checkRate(target,targetArray);
    setTargetBind(target,targetArray);
}

function strip(num, precision = 12) {
    return parseFloat(num.toPrecision(precision));
}

/**
 *
 * @param url
 * @param param
 * @param target
 */
function ajaxGetSelectValue(url,target) {
    let ajaxObj = $.getJSON(url, {}, function (json) {
        let targetObj = $(target);
        targetObj.empty();
        $.each(json, function (index, array) {
            let option = "<option value='" + array["id"] + "'>" + array["name"] + "</option>";
            targetObj.append(option);
        });
    });
}

/**
 * 以对象值做为关键字搜索
 * @param searchObj
 * @param searchUrl
 * @param showResultObj
 */
function searchByObj(searchObj,searchUrl,showResultObj) {
    let url = searchUrl + '/keyword/' + $("#"+searchObj).val();
    showPage(url,"#"+showResultObj);
}