<div class="partition">
    <div class="omnisearch_container">
        <?php echo $this->Form->create("Filter",array('class' => 'filter',"type"=>"get")); ?>
        <?php echo $this->Form->input("search",array('label'=>false)); ?>
        <?php echo $this->Form->submit("filter"); ?>
    </div>
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
</div>
<script>
    setTimeout(function(){location.reload()}, 3000);
</script>
