$(document).ready(function() {
        $.ajax({
            url: "fetch_worklog.php",
            type: "POST",
            data: {
                type: 'workingsNow',
           },
            success: function(result) {
                $("#workings-now-container").html(result);

                // Get all the Info_modal_buttons and loop through them
                var modalButtons = document.querySelectorAll(".workingsNowBtn");
                modalButtons.forEach(function(button) {
                  // Get the modal corresponding to the current button
                  var modal = button.nextElementSibling;
                
                  // Get the <span> element that closes the modal
                  var span = modal.querySelector(".close");
                
                  // When the user clicks on the button, open the modal
                  button.onclick = function() {
                    modal.style.display = "block";
                    
                        var user_id = $(this).closest('tr').find('input[name="user_id"]').val();
                    
                        $.ajax({
                            url: "fetch_tasklog.php",
                            type: "POST",
                            data: {user_id: user_id},
                            success: function(data) {
                                $('#tasklogsNow tbody').html(data);
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
            }
        });
        setInterval(function() {
            $.ajax({
                url: "fetch_worklog.php",
                type: "POST",
                data: {
                    type: 'workingsNow',
               },
                success: function(result) {
                    $("#workings-now-container").html(result);
                    // Get all the Info_modal_buttons and loop through them
                    var modalButtons = document.querySelectorAll(".workingsNowBtn");
                    modalButtons.forEach(function(button) {
                      // Get the modal corresponding to the current button
                      var modal = button.nextElementSibling;
                    
                      // Get the <span> element that closes the modal
                      var span = modal.querySelector(".close");
                    
                      // When the user clicks on the button, open the modal
                      button.onclick = function() {
                        modal.style.display = "block";
                        
                        var user_id = $(this).closest('tr').find('input[name="user_id"]').val();
                        
                        $.ajax({
                            url: "fetch_tasklog.php",
                            type: "POST",
                            data: {user_id: user_id},
                            success: function(data) {
                                $('#tasklogsNow tbody').html(data);
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
                }
            });
        }, 60000);
});

$(document).ready(function() {

    // fetch the worklog based on the selected date
    $('#date-select').change(function() {
        var date = $(this).val();
        $.ajax({
            url: "fetch_worklog.php",
            type: "POST",
            data: {
                type: 'workingsSummary',
                date: date
            },
            success: function(result) {
                $("#workings-container").html(result);
                
            // Get all the Info_modal_buttons and loop through them
            var modalButtons = document.querySelectorAll(".modal-btn");
            modalButtons.forEach(function(button) {
              // Get the modal corresponding to the current button
              var modal = button.nextElementSibling;
            
              // Get the <span> element that closes the modal
              var span = modal.querySelector(".close");
            
              // When the user clicks on the button, open the modal
              button.onclick = function() {
                modal.style.display = "block";
                
                var user_id = $(this).closest('tr').find('td input[name="user_id"]').val();
                
                $.ajax({
                    url: "fetch_tasklog.php",
                    type: "POST",
                    data: {user_id: user_id,
                           date: date
                    },
                    success: function(data) {
                        $('#tasklogsSummary tbody').html(data);

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
            }
        });
    });
    
});


$(document).ready(function() {
  // Attach a delegated event listener to the parent of the modal that contains the input fields
  $('#workingsSummarysModalChange').on('change', 'table.requests td.st input[type="datetime-local"]', function() {
    // Get the values of the input fields
    var wid = $(this).siblings('[name="wid"]').val();
    var start_time = $(this).val().replace('T', ' ');
    var end_time = $(this).parent().siblings().children('[name$="end_time"]').val().replace('T', ' ');
    var userid = $(this).siblings('[name$="userid"]').val();

    // Send the values to the server using AJAX
    $.ajax({
      url: 'update_worklog.php',
      type: 'POST',
      data: {
        wid: wid,
        start_time: start_time,
        end_time: end_time,
        userid: userid
      },
      success: function(response) {
      },
      error: function(xhr, status, error) {
        console.error('Error saving data:', error);
      }
    });
  });
  $('#workingsSummarysModalChange').on('change', 'table.requests td.et input[type="datetime-local"]', function() {
    // Get the values of the input fields
    var wid = $(this).parent().siblings().children('[name="wid"]').val();
    var end_time = $(this).val().replace('T', ' ');
    var start_time = $(this).parent().siblings().children('[name$="start_time"]').val().replace('T', ' ');
    var userid = $(this).parent().siblings().children('[name$="userid"]').val();

    // Send the values to the server using AJAX
    $.ajax({
      url: 'update_worklog.php',
      type: 'POST',
      data: {
        wid: wid,
        start_time: start_time,
        end_time: end_time,
        userid: userid
      },
      success: function(response) {
      },
      error: function(xhr, status, error) {
        console.error('Error saving data:', error);
      }
    });
  });
  
});