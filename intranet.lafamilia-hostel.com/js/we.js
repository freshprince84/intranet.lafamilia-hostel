$(document).ready(function() {
    $.ajax({
        url: "fetch_requests.php",
        type: "GET",
        success: function(data) {
            $('#requests tbody').html(data);

            // Get all the Info_modal_buttons and loop through them
            var modalButtons = document.querySelectorAll(".mod");
            
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
function archiveReq() {

    $.ajax({
        url: "fetch_requests.php",
        type: "POST",
        data: {arch: 'arch'},
        beforeSend: function() {
            
            $('.text-end').html('<button onclick="backReq()" class="btn sub_btn"><i class="bi bi-backspace"></i></button>');
            $('#requests tbody').html(lM);
        },
        success: function(data) {
            $('#requests tbody').html(data);
        }
    });
}
function backReq() {

    $.ajax({
        url: "fetch_requests.php",
        type: "GET",
        beforeSend: function() {
            
            $('.text-end').html('<button onclick="archiveReq()" class="btn sub_btn"><i class="bi bi-archive"></i></button>');
            $('#requests tbody').html(lM);
        },
        success: function(data) {
            $('#requests tbody').html(data);
        }
    });
}

function filterTable() {
  // get the input elements for each column
  var inputs = document.getElementsByClassName("search_input");

  // get the filter values for each column
  var filters = [];
  for (var i = 0; i < inputs.length; i++) {
    filters.push(inputs[i].value.toUpperCase());
  }

  // get the table body
  var table = document.getElementById("requests");
  var tbody = table.getElementsByTagName("tbody")[0];

  // get the rows in the table body
  var rows = tbody.getElementsByTagName("tr");

  // loop through the rows and hide those that don't match the filters
  for (var i = 0; i < rows.length; i++) {
    var cells = rows[i].getElementsByTagName("td");
    var showRow = true;
    for (var j = 0; j < cells.length; j++) {
      var text = cells[j].textContent || cells[j].innerText;
      if (filters[j] && text.toUpperCase().indexOf(filters[j]) === -1) {
        showRow = false;
        break;
      }
    }
    rows[i].style.display = showRow ? "" : "none";
  }
}