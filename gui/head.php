<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="Tobias Mädel, Steven Tappert">
    <meta name="robots" content="noindex">

    <title>Dark-IT.net Screenshots</title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font-Awesome CSS -->
    <link href="assets/css/font-awesome.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="assets/css/grid.css" rel="stylesheet">

    <!-- jQuery core JS -->
    <script src="assets/js/jquery-3.2.1.min.js"></script>

    <!-- Bootstrap core JS -->
    <script src="assets/js/bootstrap.min.js"></script>
</head>

<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <a class="navbar-brand" href="/gui/">
        <img src="assets/images/dark-it_logo_path_white.svg" alt="Dark-IT Logo" height="25"
             class="d-inline-block align-top"/>
        Screenshots
    </a>
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse"
            data-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNavbar">
        <ul class="navbar-nav mr-auto mt-2 mt-lg-0">

        </ul>
        <div class="form-inline my-2 my-lg-0">
            <div class="input-group has-success">
                <input class="form-control" type="text" id="searchTerm" name="searchTerm" placeholder="Suche" autofocus>
                <span class="input-group-append">
                    <button class="btn btn-outline-success" id="searchButton">
                        <i class="fa fa-search" aria-hidden="true"></i>
                    </button>
                </span>
            </div>
        </div>
    </div>
</nav>

<script>
    let searchTimer;
    let pageCount;
    const httpurl = "<?= $httpurl; ?>";

    $(function () {
        $("#searchTerm").bind("keyup paste cut", function (e) {
            var searchTerm = $(this).val();

            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                if (searchTerm.length > 1 && searchTerm.length < 4) return;

                $.post("ajax.php", {fnc: "getEntries", term: searchTerm, offset: e.offset}, function (data) {
                    // Pagination
                    $(".page").remove();
                    const pageCount = Math.ceil(data['total'] / 24);
                    const nextPage = $(".nextPage");

                    for (let i = -4; i < 5; i++) {
                        let offset = data['offset'] + i * 24;
                        if (offset < 0 || offset > data['total']) continue;

                        const newPage = $(".newPage").clone().removeClass("newPage").removeClass("d-none").addClass("page");
                        $("a", newPage).data("offset", offset);
                        $("a", newPage).html(offset / 24 + 1);
                        if (offset === data['offset']) {
                            $(newPage).addClass("active");
                        }
                        nextPage.parent().before(newPage);
                    }

                    let prevOffset = data['offset'] - 24;
                    let nextOffset = data['offset'] + 24;
                    if (prevOffset < 0) prevOffset = 0;
                    if (nextOffset > data['total']) nextOffset = pageCount * 24 - 24;
                    $(".prevPage").data("offset", prevOffset);
                    nextPage.data("offset", nextOffset);

                    $("#entries").html("");
                    $.each(data['entries'], function (index, entry) {
                        const newEntry = $(".newEntry").clone().removeClass("newEntry").removeClass("d-none");
                        const id = entry['id'];
                        const url = entry['url'];

                        $(newEntry).addClass("entry" + id);
                        $(".editTags", newEntry).data("entryid", id);
                        $(".delScreenshot", newEntry).data("entryid", id);
                        $(".screenshot", newEntry).attr("href", httpurl + url);
                        $(".screenshot img", newEntry).attr("src", "showThumb.php?img=" + url);
                        $(".screenshot img", newEntry).attr("alt", url);
                        $(".screenshot img", newEntry).attr("title", url);
                        $(".url", newEntry).html(url);

                        $(".date", newEntry).html(entry['date']);
                        $(".tags", newEntry).html(entry['tags']);

                        $("#entries").append(newEntry);
                    });
                });
            }, 400);
        });

        $("#searchButton").click(function () {
            const searchTerm = $("#searchTerm");
            searchTerm.focus();
            searchTerm.trigger({type: "keyup", which: 13, keyCode: 13, offset: 0});
        });

        $("body").on("click", ".page-link", function () {
            const offset = $(this).data("offset");
            $("#searchTerm").trigger({type: "keyup", which: 13, keyCode: 13, offset: offset});
        });
    });
</script>
