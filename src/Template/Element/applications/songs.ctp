<?php
    use Cake\Core\Configure;
?>

<h2>Songs</h2>

<p>
    Samples of your music help us get to know you, match you with similar artists, and introduce website
    visitors to your music. Before uploading any songs, carefully read the following.
</p>

<ul>
    <li>
        Submit <strong>up to <?= $songsLimit ?></strong> tracks.
    </li>
    <li>
        Submit only <strong>original music</strong> that you have full distribution rights to.
        This typically does not include cover songs.
    </li>
    <li>
        Submit <strong>mp3-formatted</strong> music.
    </li>
    <li>
        Each song's filesize can't exceed <strong><?= $fileSizeLimit ?>B</strong>.
    </li>
    <li>
        Apply a <strong>"Song Title.mp3"</strong> format to each song's filename before uploading it.
        Please remove track numbers from the filenames so we don't think they're part of a song's title.
    </li>
    <li>
        Bonus points if you
        <strong>
            fill out the file's
            <a title="ID3" href="http://en.wikipedia.org/wiki/Id3">ID3</a>
            information
        </strong>
        with your band's name, the song's title, and any other information that you can.
        <a href="https://www.google.com/search?q=how+to+edit+id3+tags">How to edit ID3 tags</a>.
    </li>
    <li>
        By submitting music tracks, you unconditionally <strong>agree to allow their redistribution</strong>
        through the festival website, festival compilation CDs, and radio promotion for
        the festival in perpetuity.
    </li>
</ul>

<div id="uploadSongContainer" <?php if (count($band['songs']) >= $songsLimit) echo 'style="display: none;"'; ?>>
    <p>
        <a href="#" id="upload_song">Upload media</a>
    </p>

    <p>
        Problems uploading? Email <a href="mailto:info@munciemusicfest.com">info@munciemusicfest.com</a>.
    </p>
</div>

<div class="alert alert-warning" id="songLimitReached" <?php if (count($band['songs']) < $songsLimit) echo 'style="display: none;"'; ?>>
    <p>
        You've reached your limit for uploading songs. :(
    </p>
    <p>
        But if you'd like to replace one with another,
        just click the checkbox under 'Delete' and submit this form to delete that track.
        Then you'll be able to upload a new one to replace it.
    </p>
</div>

<div id="uploadedSongs">
    <h3>
        Uploaded Songs
    </h3>
    <table class="table">
        <thead>
            <tr>
                <th>
                    Track title
                </th>
                <th>
                    Play
                    <br />
                    <span class="footnote">
                        (opens in new window)
                    </span>
                </th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($band['songs'] as $i => $song): ?>
                <tr>
                    <td>
                        <?= $this->Form->input(
                            "songs.$i.title",
                            [
                                'label' => false,
                                'placeholder' => 'Track title'
                            ]
                        ); ?>
                        <?= $this->Form->input("songs.$i.id"); ?>
                    </td>
                    <td>
                        <a href="/music/<?= rawurlencode($song['filename']) ?>" target="_blank">
                            <span class="glyphicon glyphicon-music" aria-hidden="true"></span>
                            <span class="sr-only">
                                Play
                            </span>
                        </a>
                    </td>
                    <td>
                        <button class="btn btn-danger btn-xs delete-song" data-song-id="<?= $song['id'] ?>">
                            Delete
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->append('buffered'); ?>
    applicationForm.initSongs({
        uploadParams: {
            fileSizeLimit: <?= json_encode($fileSizeLimit) ?>,
            timestamp: <?= time() ?>,
            token: <?= json_encode(md5(Configure::read('uploadToken').time())) ?>,
            limit: <?= $songsLimit ?>
        }
    });
<?php $this->end(); ?>
