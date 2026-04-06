$(document).ready(function () {
    // --- 1. 实时监听：防止用户删除 +60 ---
    $("#phone").on("input", function() {
        if (!this.value.startsWith("+60")) {
            this.value = "+60";
        }
        // 只允许在 +60 后面输入数字
        var prefix = "+60";
        var rest = this.value.substring(3);
        this.value = prefix + rest.replace(/[^0-9]/g, "");
    });

    // --- 2. 新增地址按钮 ---
    $(".btn-add").click(function (e) {
        e.stopPropagation(); 
        // 清空表单，但电话设为默认的 +60
        $("#address_id, #full_name, #address_line, #city, #postcode").val("");
        $("#phone").val("+60"); 
        
        $("#formTitle").text("Add New Address");
        $("#formSubmit").attr("name", "add_address").text("Add Address");
        $("#addForm").fadeIn();  
    });

    // --- 3. 编辑地址按钮 ---
    $(".btn-edit").click(function (e) {
        e.stopPropagation();
        var btn = $(this);
        
        $("#address_id").val(btn.data("address_id"));
        $("#full_name").val(btn.data("full_name"));
        $("#address_line").val(btn.data("address_line"));
        $("#city").val(btn.data("city"));
        $("#postcode").val(btn.data("postcode"));
        
        // 获取电话并确保有 +60 前缀
        var phoneVal = String(btn.data("phone"));
        if (phoneVal && !phoneVal.startsWith("+60")) {
            // 如果原本是 012...，去掉 0 换成 +60
            phoneVal = "+60" + phoneVal.replace(/^0/, "");
        }
        $("#phone").val(phoneVal);

        $("#formTitle").text("Edit Address");
        $("#formSubmit").attr("name", "update_address").text("Update Address");
        $("#addForm").fadeIn();
    });

    // --- 4. 点击空白处关闭弹窗 ---
    $(document).click(function (event) {
        if (!$(event.target).closest("#addForm, .btn-add, .btn-edit").length) {
            $("#addForm").fadeOut();  
        }
    });

    // --- 5. 表单提交最终 Validation ---
    $("#addressForm").submit(function(e) {
        var phone = $("#phone").val();
        var address = $("#address_line").val();
        var postcode = $("#postcode").val();

        // 验证马来西亚手机号：必须以 +60 开头，总长度 12-13 位
        var phonePattern = /^\+60[0-9]{9,10}$/;
        // 验证邮编：必须是 5 位数字
        var postcodePattern = /^[0-9]{5}$/;

        if (!phonePattern.test(phone)) {
            alert("Invalid Phone! Must start with +60 followed by 9-10 digits.\nExample: +60123456789");
            e.preventDefault();
            return false;
        }

        if (!postcodePattern.test(postcode)) {
            alert("Postcode must be exactly 5 digits!");
            e.preventDefault();
            return false;
        }

        if (address.trim().length < 5) {
            alert("Please provide a more detailed address (Min 5 characters).");
            e.preventDefault();
            return false;
        }
    });
});