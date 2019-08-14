function initInnoCore(){
    if(innobyte_core_admin_section == 'innobyte_core'){
        $('innobyte_core_extensions').update($('innobyte_core_extensions_hidden').innerHTML)
    }
    if(innobyte_core_admin_section == 'innobyte_shop'){
        $('innobyte_shop_extensions').update($('innobyte_shop').innerHTML)
    }
}
Event.observe(window, 'load', function() {
    initInnoCore();
});
