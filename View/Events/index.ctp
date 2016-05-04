<div class="partition">
    <div class="omnisearch_container">
        <?php echo $this->Form->create("Filter",array('class' => 'filter',"type"=>"get")); ?>
        <?php echo $this->Form->input("search",array('label'=>false)); ?>
        <button form="FilterIndexForm" type="submit">Filter</button>
        <button form="FilterIndexForm" type="submit" formaction="<?php echo Router::url(array("live")) ?>">Live on</button>
        <button form="FilterIndexForm" type="submit" formaction="<?php echo Router::url(array("action"=>"index")) ?>">Live off</button>
        <?php echo $this->Form->end(); ?>
    </div>
    <?php if(count($events) > 0) : ?>
    <table class="">
        <thead>
            <tr>
                <th>Date</th>
                <th>Source</th>
                <th>Severity</th>
                <th>Event</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($events as $event): ?>
            <tr>
                <td><?php echo $event['Event']['date'] ?></td>
                <td><?php echo $event['Event']['source'] ?></td>
                <td><?php echo $event['Event']['severity'] ?></td>
                <td><?php echo $event['Event']['event'] ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div>
    <?php echo $this->Paginator->numbers(); ?>
    </div>
    <?php else: ?>
    No Events
    <?php endif; ?>
</div>
<?php if($live) : ?>
<script>
    setTimeout(function(){location.reload()}, 3000);
</script>
<?php endif; ?>

