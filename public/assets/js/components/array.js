
$.Tapioca.Components.Array = 
{
    get: function(obj, str)
    {
        str = str.split('.');
        for (var i = 0; i < str.length; i++)
            obj = obj[str[i]];
        return obj;
    }
}