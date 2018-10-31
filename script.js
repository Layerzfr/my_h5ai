$(document).on("click", ".menu", function () {
    const directory = $(this);
    // if($(this).hasClass('file')){
    //     return false;
    // }
    if ($(this).parent().has("li").length) {
        deleteList($(this).parent(), $(this));
        $(this).attr("class", "menu fa fa-chevron-right");
    } else {
        $.ajax({
            type: "POST",
            url: "index.php",
            data: {dir: directory.attr("id")},
            dataType: "json",
            error: function (request, error) {
                console.log("Erreur :" + request.responseText);
            },
            success: function (data) {
                console.log(data);
                    $.each(data["file"], function (y) {
                        directory.after("<ul><li><a class='menu file' href='#' id='" + directory.attr('id') + "/" + data["file"][y] + "'>" + data["file"][y] + "</a></li></ul>")
                        directory.attr("class", "menu fa fa-chevron-down");
                    });
                    $.each(data["folder"], function (y) {
                        directory.after("<ul><li><a class='menu fa fa-chevron-right' href='#' id='" + directory.attr('id') + "/" + data["folder"][y] + "'>" + data["folder"][y] + "</a></li></ul>")
                        directory.attr("class", "menu fa fa-chevron-down");
                    });
            }
        });
    }

    function deleteList($dom, $link) {
        $($dom).html($link);
    }


});