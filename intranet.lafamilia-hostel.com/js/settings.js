function set_tab_switch(evt, quiName) {
  // Declare all variables
  var i, tabcontent, tablinks;

  // Get all elements with class="tabcontent" and hide them
  tabcontent = document.getElementsByClassName("tabcontent");
  for (i = 0; i < tabcontent.length; i++) {
    tabcontent[i].style.display = "none";
  }

  // Get all elements with class="tablinks" and remove the class "active"
  tablinks = document.getElementsByClassName("tablinks");
  for (i = 0; i < tablinks.length; i++) {
    tablinks[i].className = tablinks[i].className.replace(" active", "");
  }

  // Show the current tab, and add an "active" class to the button that opened the tab
  document.getElementById(quiName).style.display = "block";
  evt.currentTarget.className += " active";
} 

// Get the element with id="defaultOpen" and click on it
document.getElementById(activeId).click();




document.getElementById("user-dropdown").addEventListener("change", function () {
    const selectedUserId = this.value;
    const viewContent = document.getElementById("view-content")
    if (selectedUserId) {
        $.ajax({
          url: "fetch_users.php",
          type: "POST",
          data: {userid: selectedUserId},
          success: function(data) {
            $('.user-edit').html(data);
          },
          error: function(xhr, status, error) {
          }
        });
        viewContent.style.display = "block";
    } else {
        viewContent.style.display = "none";
    }
});

// Archive Buttons
let lM = `<tr>
                <td>
                   <div style="display: flex; justify-content: center; align-items: center; position: absolute; top: 20%; left: 50%; transform: translate(-50%, -50%);">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </td>
            </tr>`;            
function archiveUsers() {
    $.ajax({
        url: "fetch_users.php",
        type: "POST",
        data: {arch: 'arch'},
        beforeSend: function() {
            $('.text-end').html('<button onclick="backUsers()" class="btn"><i class="bi bi-backspace"></i></button>');
            $('#settings_users tbody').html(lM);
        },
        success: function(data) {
            console.log(data);
            $('#settings_users tbody').html(data);
            addModalEventListeners();
        }
    });
}
function backUsers() {
    $.ajax({
        url: "fetch_users.php",
        type: "GET",
        beforeSend: function() {
            $('.text-end').html('<button onclick="archiveUsers()" class="btn"><i class="bi bi-archive"></i></button>');
            $('#settings_users tbody').html(lM);
        },
        success: function(data) {
            $('#settings_users tbody').html(data);
            addModalEventListeners();
        }
    });
}