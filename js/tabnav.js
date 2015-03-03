$(function() {
    if (window.location.hash != "") {
        var a = window.location.hash.split("tab_");
        $('.nav-tabs a[href="#' + a[1] + '"]').tab("show")
    }
    $(".nav-tabs a").on("shown.bs.tab", function(a) {
        window.location.hash = "tab_" + a.target.hash.substring(1)
    });
    $(".tabtrigger").click(function(e) {
        e.preventDefault();
        $('.nav-tabs a[href="' + $(this).attr("href") + '"]').tab("show")
    })
})
