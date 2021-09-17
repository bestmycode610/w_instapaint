<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<form method="post" action="{url link='admincp.forum.delete'}">
    <div class="panel panel-default">
        <div class="panel-body">
            <div><input type="hidden" name="delete" value="{$iDeleteId}" /></div>
            <div class="alert alert-warning">
                {_p('are_you_sure_you_want_to_delete_this_forum')}
            </div>
            {if $iTotalItems || $iTotalSubs}
                <div class="form-group">
                    <label>{_p('select_an_action_to_apply_for_all_threads_as_well_as_sub_forums_belonging_to_this_forum')}</label>
                        <div class="radio">
                            <label><input type="radio" onchange="core_forums_onchangeDeleteForumType(1)" name="val[delete_type]" id="delete_type" value="1" checked>{_p('remove_all_threads_and_sub_forums_belonging_to_this_forum')}</label>
                        </div>
                    {if !empty($sForums)}
                        <div class="radio">
                            <label><input type="radio" onchange="core_forums_onchangeDeleteForumType(2)" name="val[delete_type]" id="delete_type" value="2">{_p('select_another_forums_to_move_all_threads_and_sub_forums_belonging_to_this_forum')}</label>
                        </div>
                        <select name="val[new_forum_id]" id="forum_select" class="form-control" style="display: none">
                            {$sForums}
                        </select>
                    {/if}
                </div>
            {else}
                <div><input type="hidden" name="val[delete_type]" value="0" /></div>
            {/if}
        </div>
        <div class="panel-footer">
            <input type="submit" value="{_p('Submit')}" class="btn btn-primary" />
            <input onclick="return js_box_remove(this);" type="submit" value="{_p('Cancel')}" class="btn btn-default" />
        </div>
    </div>
</form>