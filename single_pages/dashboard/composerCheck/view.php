<?php
defined('C5_EXECUTE') or die(_("Access Denied."));
$form = Core::make('helper/form');
$ps = Core::make('helper/form/page_selector');

if(isset($checks)):
    $canMap = false;
    ?>
    <form method="post" class="check-form" action="<?php echo $this->action('next')?>">
        <?= t('Parent page ID').' : ' .$page;?><input type="hidden" name="page" value="<?= $page;?>"><br>
        <hr>
        <?php
        foreach($checks as $check):
            ?>
            <p>
                <?php
                echo '<strong>- '.$check['pageID'].'</strong> : '.$check['pageName'].'<br>';

                $defaultBlocks = $check['defaultBlocksInComposers'];
                if($defaultBlocks):
                    echo '<strong>&nbsp;&nbsp;&nbsp;'.t('Default blocks in composer').'</strong><br>';
                    foreach($defaultBlocks as $db):
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.$db.'<br>';
                    endforeach;
                endif;

                $blocksInAreas = $check['blocksInAreas'];
                if($blocksInAreas):
                    echo '<strong>&nbsp;&nbsp;&nbsp;'.t('Blocks in Main area').'</strong><br>';
                    foreach($blocksInAreas as $db):
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.$db['name'].'<br>';

                        if($db['inComposer'] === true):
                            echo '<small style="color:green">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.t('In composer').'</small><br>' ;
                        else:
                            echo '<small style="color:red">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.t('Not in composer').'</small><br>' ;
                        endif;

                        if($db['isMapped'] == true):
                            echo '<small style="color:green">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.t('Is mapped').'</small><br>' ;
                        else:
                            echo '<small style="color:red">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- '.t('Not mapped').'</small><br>' ;
                        endif;

                        if($db['inComposer'] && !$db['isMapped'])
                            $canMap = true;
                    endforeach;
                endif;

                ?>
            </p>
            <hr>
            <?php
        endforeach;
        ?>
        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <button class="btn" type="submit" name="submit" id="submit" value="reset"><?php echo t('Reset mapped composer')?></button>
                <?php
                $canMap = true;
                if($canMap):
                    ?><button class="pull-right btn btn-primary" type="submit" name="submit" id="submit" value="fix"><?php echo t('Map with composer')?></button>
                    <?php
                endif;
                ?>
            </div>
        </div>
    </form>
    <?php
else:
    ?>
    <form method="post" class="check-form" action="<?php echo URL::to('/dashboard/composerCheck')?>">
        <div class="form-group">
            <?= $form->label('redirection' , t('Page (s) to check')); ?>
            <?= $ps->selectPage('page', ''); ?>
        </div>

        <div class="ccm-dashboard-form-actions-wrapper">
            <div class="ccm-dashboard-form-actions">
                <button class="pull-right btn btn-primary" type="submit" name="submit" id="submit" value="check"><?php echo t('Check')?></button>
            </div>
        </div>
    </form>
    <script>
        $(function() {
            $(document).on('submit', '.check-form', function (e) {
                var page = $('input[name="page"]').val();
                if(page == 0) {
                    alert('<?php echo t('You must select a page.');?>');
                    return false;
                }
                else{
                    return true;
                }
            });
        });
    </script>
    <?php
endif;
?>



