
_.strftime.i18n.fr = {
    fullMonths: ['Janvier', 'Fevrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Decembre'],
    shortMonths: ['Jan', 'Fev', 'Mar', 'Avr', 'Mai', 'Jui', 'Juil', 'Aou', 'Sep', 'Oct', 'Nov', 'Dec'],
    fullWeekdays: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
    shortWeekdays: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
    ampm: ['AM', 'PM'],
    dateTimeFormat: '%a %b %e %H:%M:%S %Y',
    dateFormat: '%m/%d/%y',
    timeFormat: '%H:%M:%S',
    qualifier: ['', 'environ', 'moins d\'un', 'moitié'],
    measure: ['seconde', 'minute', 'heure', 'jour', 'mois', 'année']
};

_.distanceOfTimeInWords.i18n.fr = function (qualifier, count, measure) 
{
    if (count == 1 && measure == 'minute' && (qualifier == 'less than' || qualifier == 'half')) {
        count = 'a';
    }
    return [
        qualifier,
        count,
        measure + (count > 1 ? 's' : '')
    ].join(' ').trim();
}

_.distanceOfTimeInWords.i18n.locale = 'fr';
_.strftime.i18n.locale = 'fr';
