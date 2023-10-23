$(document).ready(function() {
    $.ajax({
        url: "fetch_roles.php",
        type: "POST",
        data: {userid: userId,
               login: 0
        },
        success: function(data) {
            $('#header-role-DD').html(data);
        }
    });
});
$('#header-role-DD').change(function() {
    
// Get the select element by ID or class name
var selectElement = document.querySelector('#header-role-DD select');

// Get the selected option value
var selectedRole = selectElement.value;

    $.ajax({
        url: "set_role_session.php",
        type: "POST",
        data: {role: selectedRole},
        success: function(data) {
                window.location.href = home;
        }
    });
});