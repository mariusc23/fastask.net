/*****************************************************************************/
/*
/* Planner JS
/*
/*****************************************************************************/
/**
 * Called from main.js at end of setup
 */
function init_minibox() {

var
/*-------------- CONSTANTS --------------*/
      PLANNER_BOX = $('\
    <div id="mini" class="mini-box box" rel="1"> \
    <div class="loading"></div> \
    <h1 class="title">planner</h1> \
    <div class="tabs"> \
        <div class="icon planner" title="stuff witout a due date"> \
            <a href="#l=1"></a><span class="c"></span></div> \
        <div class="icon trash" title="bleh, garbage"> \
            <a href="#l=2"></a><span class="c"></span></div> \
    </div> \
    <div class="table" cellspacing="0"> \
    </div> \
    </div><!-- planner-box -->')
/*-------------- VARIABLES --------------*/
;

PLANNER_BOX.appendTo('#content');

} // end init_planner