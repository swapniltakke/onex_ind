// Please see documentation at https://docs.microsoft.com/aspnet/core/client-side/bundling-and-minification
// for details on configuring this project to bundle and minify static web assets.

// Write your JavaScript code.
$(document).ready(function () {
    $("#pastLabels .buttons").each(function () {
        $(this).bind("click", function () {
            //Put your code here
            alert($(this).find("#serialNumber"));
            alert($(this).find("#serialNumber").val());
        });
    });
});