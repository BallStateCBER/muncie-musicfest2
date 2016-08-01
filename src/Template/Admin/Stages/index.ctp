<p>
    <?= $this->Html->link(
        'Add New Stage',
        ['action' => 'add'],
        ['class' => 'btn btn-default']
    ) ?>
</p>

<table class="table" id="admin-stages">
    <thead>
        <tr>
            <th>Stage</th>
            <th>Slots</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($stages as $stage): ?>
        <tr>
            <td>
                <strong><?= h($stage->name) ?></strong>
                <br />
                <?= nl2br($stage->address) ?>
                <?php if ($stage->age_restriction): ?>
                    <br />
                    21+
                <?php endif; ?>
            </td>
            <td>
                <?php if ($stage->slots): ?>
                    <ul>
                        <?php foreach ($stage->slots as $slot): ?>
                            <?php if ($slot->time->format('a') == 'pm'): ?>
                                <li>
                                    <?= $slot->time->format('g:ia') ?> -
                                    <?php if ($slot->band): ?>
                                        <?= $slot->band->name ?>
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <?php foreach ($stage->slots as $slot): ?>
                            <?php if ($slot->time->format('a') == 'am'): ?>
                                <li>
                                    <?= $slot->time->format('g:ia') ?> -
                                    <?php if ($slot->band): ?>
                                        <?= $slot->band->name ?>
                                    <?php endif; ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </td>
            <td class="actions">
                <?= $this->Html->link(
                    __('Edit Stage'),
                    ['action' => 'edit', $stage->id],
                    ['class' => 'btn btn-default']
                ) ?>
                <?php if ($authUser['id'] == 1): ?>
                    <?= $this->Form->postLink(
                        __('Delete Stage'),
                        [
                            'action' => 'delete',
                            $stage->id
                        ],
                        [
                            'confirm' => __('Are you sure you want to delete this stage? This will cause UTTER CHAOS.'),
                            'class' => 'btn btn-default'
                    ]) ?>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
