<h2>
    <?= ($band->application_step == 'finalize') ? 'Almost done!' : 'Message to MMF Organizers' ?>
</h2>
<p>
    If you have a message for the Muncie MusicFest organizers, here's where you include it.
</p>
<?= $this->Form->input('message', [
    'label' => false,
    'type' => 'textarea'
]) ?>
