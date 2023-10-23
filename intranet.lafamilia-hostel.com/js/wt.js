$(document).ready(function() {
    $.ajax({
        url: "fetch_personal_tasks.php",
        type: "POST",
        data: {started: started},
        success: function(data) {
            $('#personal_tasks tbody').html(data);
        }
    });
    $.ajax({
        url: "fetch_tasks.php",
        type: "POST",
        data: {started: started},
        success: function(data) {
            $('#tasks tbody').html(data);
            
            // Get all the worktimes_modal_buttons and loop through them
            var modalButtons = document.querySelectorAll(".btn.sub_btn");
            modalButtons.forEach(function(button) {
                // Get the modal corresponding to the current button
                var modal = button.nextElementSibling;
            
                // Get the <span> element that closes the modal
                var span = modal.querySelector(".close");
            
                // When the user clicks on the button, open the modal
                button.onclick = function() {
                  modal.style.display = "block";
                  
                    $.ajax({
                        url: "fetch_worklog.php",
                        type: "GET",
                        success: function(data) {
                            $('#worklogs tbody').html(data);
                        }
                    });                  
                    $.ajax({
                        url: "fetch_tasklog.php",
                        type: "GET",
                        success: function(data) {
                            $('#tasklogs tbody').html(data);
                        }
                    });                  
                }
            
                // When the user clicks on <span> (x), close the modal
                span.onclick = function() {
                    modal.style.display = "none";
                }
            
                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            });            
            
            // Get all the Info_modal_buttons and loop through them
            modalButtons = document.querySelectorAll(".taskMod");
            
            modalButtons.forEach(function(button) {
                // Get the modal corresponding to the current button
                var modal = button.nextElementSibling;
              
                // Get the <span> element that closes the modal
                var span = modal.querySelector(".close");
    
                // Get the hoverInfos and hoverInfo spans corresponding to the current button
                var modalHoverSpans = document.querySelectorAll(".hoverInfo");
    
                // When the user clicks on the button, open the modal and hide hoverInfos
                button.onclick = function() {
    
                    modal.style.display = "block";
                    modalHoverSpans.forEach(function(span) {
                        span.style.display = "none";
                    });
                }
            
                // When the user clicks on <span> (x), close the modal
                span.onclick = function() {
                    modal.style.display = "none";
                }
            
                // When the user clicks anywhere outside of the modal, close it
                window.onclick = function(event) {
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            });            
        }
    });
    setInterval(function() {
        $.ajax({
            url: "fetch_worklog.php",
            type: "GET",
            success: function(data) {
                $('#worklogs tbody').html(data);
            }
        });
    }, 60000);
});

let lM = `<tr>
                <td>
                   <div style="display: flex; justify-content: center; align-items: center; position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%);">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </td>
            </tr>`; 
function archivePT() {
    $.ajax({
        url: "fetch_personal_tasks.php",
        type: "POST",
        data: {arch: 'arch',
               started: started},
        beforeSend: function() {
            $('.text-end').html('<button onclick="backPT()" class="btn"><i class="bi bi-backspace"></i></button>');
            $('#personal_tasks tbody').html(lM);
        },
        success: function(data) {
            $('#personal_tasks tbody').html(data);
        }
    });
}
function backPT() {
    $.ajax({
        url: "fetch_personal_tasks.php",
        type: "POST",
        data: {started: started},
        beforeSend: function() {
            $('.text-end').html('<button onclick="archivePT()" class="btn"><i class="bi bi-archive"></i></button>');
            $('#personal_tasks tbody').html(lM);
        },
        success: function(data) {
            $('#personal_tasks tbody').html(data);
        }
    });
}

$('#task-role-DD').change(function() {
    
// Get the select element by ID or class name
var selectElement = document.querySelector('#task-role-DD select');

// Get the selected option value
var selectedRole = selectElement.value;

    $.ajax({
        url: "set_role_session.php",
        type: "POST",
        data: {role: selectedRole},
        success: function(data) {
                window.location.href = "worktracker.php";
        }
    });
});