<?php
require_once('config.php');
require_once('../lib.php');
require('head.php');
?>

<div class="album text-muted">
	<div class="container-fluid">
		<!-- Screenshots -->
		<div class="row align-content-baseline justify-content-center" id="entries">
			<!-- Trigger the search -->
			<script> $( function() { $( "#searchTerm" ).trigger({ type:"keyup", which:13, keyCode:13, offset:0 }); } ); </script>
		</div><!-- // Screenshots -->

		<!-- Pagination -->
		<nav>
			<ul class="pagination justify-content-center">
				<li class="page-item">
					<a class="prevPage page-link"  href="javascript:void(0)" data-offset="0">&laquo;</a>
				</li>

				<li class="newPage page-item d-none">
					<a class="page-link" href="javascript:void(0)" data-offset="0"></a>
				</li>

				<li class="page-item">
					<a class="nextPage page-link" href="javascript:void(0)" data-offset="0">&raquo;</a>
				</li>
			</ul>
		</nav><!-- // Pagination -->
	</div>
</div>

<?php
require('screenshot-entry.php');
require('modal-edit-tags.php');
require('footer.php');