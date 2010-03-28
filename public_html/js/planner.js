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
      PLANER_BOX = $('\
    <div id="plan" class="planner-box"> \
    <div class="loading"></div> \
    <h1 class="title">planner</h1> \
    <div class="planner-table" cellspacing="0"> \
    </div> \
    </div><!-- planner-box -->')
/*-------------- VARIABLES --------------*/
;

PLANER_BOX.appendTo('#content');

} // end init_planner