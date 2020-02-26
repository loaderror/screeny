<div class="card newEntry d-print-none d-none col-md">
    <a class="editTags text-info" href="javascript:void(0)" data-toggle="modal" data-target="#editTagsModal"
       data-entryID="" alt="Tags bearbeiten" title="Tags bearbeiten">
		<span class="fa-stack">
			<i class="fa fa-circle fa-stack-2x"></i>
			<i class="fa fa-tags fa-stack-1x fa-inverse"></i>
		</span>
    </a>

    <a class="delScreenshot text-danger" href="javascript:void(0)" data-entryID="" alt="Screenshot löschen"
       title="Screenshot löschen">
		<span class="fa-stack">
			<i class="fa fa-circle fa-stack-2x"></i>
			<i class="fa fa-trash fa-stack-1x fa-inverse"></i>
		</span>
    </a>

    <a class="screenshot" href="" target="_blank">
        <img src="" alt="" title="" class="card-img-top">
    </a>

    <div class="card-body">
        <h5 class="card-title">
            <span class="url"></span>
        </h5>

        <h6 class="card-subtitle mb-2 text-muted">
            <span class="date"></span>
        </h6>

        <div class="card-text">
            <div class="tags font-italic"></div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $("body").on("click", ".delScreenshot", function () {
            const entryID = $(this).data("entryid");
            const offset = $(".page-item.active .page-link").data("offset");

            $.post("ajax.php", {fnc: "delEntry", entryID: entryID}, function (data) {
                $("#searchTerm").trigger({type: "keyup", which: 13, keyCode: 13, offset: offset});
            });
        });
    });
</script>