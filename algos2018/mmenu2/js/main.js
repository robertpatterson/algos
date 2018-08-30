


var $menu = $('#menu');
var $btnMenu = $('.btn-menu');
var $img = $('img');


var api = $menu.data("mmenu");

$btnMenu.click(function() {
    api.open();
});



$menu.find( ".mm-next" ).addClass("mm-fullsubopen");


api.bind('opening', function() {
    $img.attr('src', 'arrows_remove.svg');
});
api.bind('closing', function() {
    $img.attr('src', 'arrows_hamburger.svg');
});

$menu.mmenu({
    counters: true,
    navbar: {
        title: "Menu Content"
    },
    extensions: ["pageshadow", "effect-zoom-menu", "effect-zoom-panels"],
    offCanvas: {
        position  : "right",
        zposition : "back"
    }
});




api.bind('opening', function() {
    $img.attr('src', 'arrows_remove.svg');
});
api.bind('closing', function() {
    $img.attr('src', 'arrows_hamburger.svg');
});