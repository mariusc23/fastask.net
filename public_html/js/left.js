/*****************************************************************************/
/*
/* Planner JS
/*
/*****************************************************************************/
/**
 * Called from main.js at end of setup
 */
function init_planner() {

var
/*-------------- CONSTANTS --------------*/
      PLANNER_BOX = $('\
    <div id="plan" class="planner-box box"> \
    <div class="loading"></div> \
    <h1 class="title">planner</h1> \
    <div class="planner-table" cellspacing="0"> \
    </div> \
    </div><!-- planner-box -->')
      TRASH_BOX = $('\
    <div id="trash" class="trash-box box"> \
    <div class="loading"></div> \
    <h1 class="title">trash</h1> \
    <div class="trash-table" cellspacing="0"> \
    </div> \
    </div><!-- planner-box -->')
/*-------------- VARIABLES --------------*/
;

PLANNER_BOX.appendTo('#content');
TRASH_BOX.appendTo('#content');

} // end init_planner