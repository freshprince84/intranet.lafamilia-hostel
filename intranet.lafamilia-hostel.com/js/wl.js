function openQuinzena(evt, quiName) {
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


// Get all the bankInfo_modal_buttons and loop through them
var modalButtons = document.querySelectorAll(".btn.sub_btn");
modalButtons.forEach(function(button) {
  // Get the modal corresponding to the current button
  var modal = button.nextElementSibling;

  // Get the <span> element that closes the modal
  var span = modal.querySelector(".close");

  // When the user clicks on the button, open the modal
  button.onclick = function() {
    modal.style.display = "block";
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

//$('#input-field').on('input', function() {
//  var inputVal = $(this).val();
//  $.ajax({
//    url: 'save-data.php',
//    type: 'POST',
//    data: { inputVal: inputVal },
//    success: function(response) {
//      console.log(response); // Handle the response from the PHP script
//    }
//  });
//});