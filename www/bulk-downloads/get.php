<?
require_once('Core.php');

use function Safe\apcu_fetch;

$collection = null;
$collectionUrlName = HttpInput::Str(GET, 'collection', false);
$collection = null;
$authorUrlName = HttpInput::Str(GET, 'author', false);
$canDownload = false;

try{
	if($GLOBALS['User'] !== null && $GLOBALS['User']->Benefits->CanBulkDownload){
		$canDownload = true;
	}

	if($collectionUrlName !== null){
		$collections = [];

		// Get all collections and then find the specific one we're looking for
		try{
			$collections = apcu_fetch('bulk-downloads-collections');
		}
		catch(Safe\Exceptions\ApcuException $ex){
			$result = Library::RebuildBulkDownloadsCache();
			$collections = $result['collections'];
		}

		foreach($collections as $c){
			if($c->UrlLabel == $collectionUrlName){
				$collection = $c;
				break;
			}
		}

		if($collection === null){
			throw new Exceptions\InvalidCollectionException();
		}
	}

	if($authorUrlName !== null){
		$authors = [];

		// Get all authors and then find the specific one we're looking for
		try{
			$collections = apcu_fetch('bulk-downloads-authors');
		}
		catch(Safe\Exceptions\ApcuException $ex){
			$result = Library::RebuildBulkDownloadsCache();
			$collections = $result['authors'];
		}

		foreach($collections as $c){
			if($c->UrlLabel == $authorUrlName){
				$collection = $c;
				break;
			}
		}

		if($collection === null){
			throw new Exceptions\InvalidAuthorException();
		}
	}
}
catch(Exceptions\InvalidUserException $ex){
	$exception = new Exceptions\InvalidPatronException();
}
catch(Exceptions\InvalidAuthorException $ex){
	Template::Emit404();
}
catch(Exceptions\InvalidCollectionException $ex){
	Template::Emit404();
}

?><?= Template::Header(['title' => 'Download ', 'highlight' => '', 'description' => 'Download zip files containing all of the Standard Ebooks released in a given month.']) ?>
<main>
	<section class="bulk-downloads">
		<h1>Download the <?= $collection->Label ?> Collection</h1>
		<? if($canDownload){ ?>
			<p>Select the ebook format in which you’d like to download this collection.</p>
			<p>You can also read about <a href="/help/how-to-use-our-ebooks#which-file-to-download">which ebook format to download</a>.</p>
		<? }else{ ?>
			<p><a href="/about#patrons-circle">Patrons circle members</a> can download zip files containing all of the ebooks in a collection. You can <a href="/donate#patrons-circle">join the Patrons Circle</a> with a small donation in support of our continuing mission to create free, beautiful digital literature.</p>
		<? } ?>
		<?= Template::BulkDownloadTable(['label' => 'Collection', 'collections' => [$collection]]); ?>
	</section>
</main>
<?= Template::Footer() ?>
