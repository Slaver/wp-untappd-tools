<div class="wrap">
    <h1>Untappd Tools</h1>

    <div id="poststuff" class="metabox-holder has-right-sidebar">
        <div class="inner-sidebar">
            <div id="side-sortables" class="meta-box-sortabless ui-sortable" style="position:relative;">

                <div id="sm_pnres" class="postbox">
                    <h3 class="hndle"><span>Database stats</span></h3>
                    <div class="inside">
                        <p>
                            Total checkins: <strong><?php echo $checkinsTotal; ?></strong><br>
                            Average rating: <strong><?php echo round($ratingTotal, 2); ?></strong>
                        </p>
                    </div>
                </div>

                <div id="sm_pnres" class="postbox">
                    <h3 class="hndle"><span>Last 7 days</span></h3>
                    <div class="inside">
                        <p>
                            Total checkins: <strong><?php echo $checkinsDays7; ?></strong><br>
                            Average rating: <strong><?php echo round($ratingDays7, 2); ?></strong>
                        </p>
                    </div>
                </div>

                <div id="sm_pnres" class="postbox">
                    <h3 class="hndle"><span>Last 30 days</span></h3>
                    <div class="inside">
                        <p>
                            Total checkins: <strong><?php echo $checkinsDays30; ?></strong><br>
                            Average rating: <strong><?php echo round($ratingDays30, 2); ?></strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="has-sidebar sm-padded">

            <div id="post-body-content" class="has-sidebar-content">

                <div class="meta-box-sortabless">

                    <div class="postbox">
                        <h3 class="hndle"><span>Loyal users</span></h3>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div style="margin-right: 5rem">
                                <h3>Checkins</h3>
                                <ol>
                                    <?php
                                    foreach ($usersPopular as $up) { ?>
                                        <li><?php echo $up['name'] ?> — <?php echo $up['counter'] ?></li>
                                    <?php } ?>
                                </ol>
                            </div>
                            <div>
                                <h3>Rating</h3>
                                <ol style="column-count: 2; column-gap: 20px;">
                                    <?php foreach ($usersLoyal as $up) { ?>
                                        <li><?php echo $up['name'] ?> — <?php echo round($up['average'], 3) ?> (<?php echo $up['counter'] ?>)</li>
                                    <?php } ?>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle"><span>Haters</span></h3>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div>
                                <h3>Hahahaters</h3>
                                <ol style="column-count: 2; column-gap: 50px;">
                                    <?php foreach ($usersHaters as $up) { ?>
                                        <li><?php echo $up['name'] ?> — <?php echo round($up['average'], 3) ?> (<?php echo $up['counter'] ?>)</li>
                                    <?php } ?>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle"><span>Loyal places</span></h3>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div style="margin-right: 5rem">
                                <h3>Last 30 days checkins</h3>
                                <ol>
                                    <?php foreach ($placesPopularDays30 as $pp) { ?>
                                        <li><?php echo $pp['name'] ?> — <?php echo $pp['counter'] ?></li>
                                    <?php } ?>
                                </ol>
                            </div>
                            <div>
                                <h3>Last 30 days rating</h3>
                                <ol>
                                    <?php foreach ($placesLoyalDays30 as $pp) { ?>
                                        <li><?php echo $pp['name'] ?> — <?php echo round($pp['average'], 3) ?> (<?php echo $pp['counter'] ?>)</li>
                                    <?php } ?>
                                </ol>
                            </div>
                        </div>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div style="margin-right: 5rem">
                                <h3>Total checkins</h3>
                                <ol>
                                    <?php foreach ($placesPopular as $pp) { ?>
                                        <li><?php echo $pp['name'] ?> — <?php echo $pp['counter'] ?></li>
                                    <?php } ?>
                                </ol>
                            </div>
                            <div>
                                <h3>Total rating</h3>
                                <ol>
                                    <?php foreach ($placesLoyal as $pp) { ?>
                                        <li><?php echo $pp['name'] ?> — <?php echo round($pp['average'], 3) ?> (<?php echo $pp['counter'] ?>)</li>
                                    <?php } ?>
                                </ol>
                            </div>
                        </div>
                    </div>

                    <div class="postbox">
                        <h3 class="hndle"><span>Purchased</span></h3>
                        <div class="inside" style="display: flex; flex-wrap: wrap;">
                            <div style="margin-right: 5rem">
                                <h3>Last 30 days</h3>
                                <ol>
                                    <?php foreach ($placesPopularPurchasedDays30 as $pp) { ?>
                                        <li><?php echo $pp['name'] ?> — <?php echo $pp['counter'] ?></li>
                                    <?php } ?>
                                </ol>
                            </div>
                            <div>
                                <h3>Total</h3>
                                <ol>
                                    <?php foreach ($placesPopularPurchased as $pp) { ?>
                                        <li><?php echo $pp['name'] ?> — <?php echo $pp['counter'] ?></li>
                                    <?php } ?>
                                </ol>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>