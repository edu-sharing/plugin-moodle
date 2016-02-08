function loadAScript(scriptname) {
    var snode = document.createElement('script');
    snode.type = 'text/javascript';
    snode.async = true;
    snode.src = scriptname.replace(/&amp;/g, '&');
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(snode,s);
}
