// Show the team member description when clicking the 'Read More' button
$(document).on("click", ".read_more_btn", function() {
    // Check if it's already expanded
    if ($(this).hasClass("expanded")) {
        $(this).removeClass("expanded"); // Remove the class 'expanded' to the button
        $(this).parent(".col").children(".description_wrap").css("display", "none"); // Hide the description
        $(this).html("Read More"); // Change the button text
    } else {
        $(this).addClass("expanded"); // Add the class 'expanded' to the button
        $(this).parent(".col").children(".description_wrap").css("display", "block"); // Show the description
        $(this).html("Read Less"); // Change the button text
    }

});
