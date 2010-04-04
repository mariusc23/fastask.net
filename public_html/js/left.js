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
    <div id="plan" class="planner-box box" rel="1"> \
    <div class="loading"></div> \
    <h1 class="title">planner</h1> \
    <div class="planner-table table" cellspacing="0"> \
    </div> \
    </div><!-- planner-box -->')
      TRASH_BOX = $('\
    <div id="trash" class="trash-box box" rel="2"> \
    <div class="loading"></div> \
    <h1 class="title">trash</h1> \
    <div class="trash-table table" cellspacing="0"> \
    </div> \
    </div><!-- planner-box -->')
/*-------------- VARIABLES --------------*/
;

PLANNER_BOX.appendTo('#content');
TRASH_BOX.appendTo('#content');

} // end init_planner