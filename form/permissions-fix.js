var joomlaGetUrlParam = getUrlParam;
getUrlParam = function(){
    if(arguments[0] == 'id' && !joomlaGetUrlParam('id')){
        return joomlaGetUrlParam.apply(document, ['cid[]']);
    }else{
        return joomlaGetUrlParam.apply(document, arguments);
    }
};