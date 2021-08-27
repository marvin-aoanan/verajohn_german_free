<!-- Modal -->
<div class="modal fade bg-alt-pri" id="gameModal" tabindex="-1" role="dialog" aria-labelledby="gameModal" aria-hidden="true">
    <div class="modal-dialog momodel modal-fluid" role="document">
        <div class="modal-content bg-alt-sec">
            <div class=" modal-header bg-alt-sec text-center">
                <h5 class="modal-title w-100"><?php wp_title(''); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onClick="window.location.reload();">
                    <span style="font-size: 1.3em;" aria-hidden="true">&times;</span>
                </button>
            </div>
            
            <?php include (TEMPLATEPATH . '/inc/ads/ad_leaderboard.php' ); ?>
                    
            <div class="modal-body bg-alt-sec animated fadeIn delay-2s">
               <iframe id="gameLayer" src="<?php the_field('registration_page','options'); ?>" width="100%" height="100%"></iframe>
            </div>
            
            <div class="modal-footer">               
                <?php include (TEMPLATEPATH . '/inc/copyright.php' ); ?>
                <?php /*?>
                <button type="button" class="btn btn-danger btn-md btn-rounded" data-dismiss="modal">Close</button>
                <?php */?>
            </div>
            
        </div>
    </div>
</div>
<!-- END Modal -->	