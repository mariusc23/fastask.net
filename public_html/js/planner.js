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
    <div class="planner-box"> \
    <div class="loading"></div> \
    <h1 class="title">planner</h1> \
    </div><!-- planner-box -->')
/*-------------- VARIABLES --------------*/
;

PLANER_BOX.appendTo('#content');

} // end init_planner