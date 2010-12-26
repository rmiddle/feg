function addTableRow(jQtable){
    jQtable.each(function(){
        var $table = $(this);
        // Number of td's in the last table row
        var n = $('tr:last td', this).length;
        var tds = '<tr>';
        for(var i = 0; i < n; i++){
            tds += '<td>&nbsp;</td>';
        }
        tds += '</tr>';
        if($('tbody', this).length > 0){
            $('tbody', this).append(tds);
        }else {
            $(this).append(tds);
        }
    });
}
