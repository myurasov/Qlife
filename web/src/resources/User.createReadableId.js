/**
 * Readable user id generation
 *
 * @copyright 2012, Mikhail Yurasov
 */

return (function () {
  var formatId = function (id) {
      return id.replace(/\W/g, '-').replace(/-+/g, '-').toLowerCase();
    };

  var idExists = function (id) {
      // filter reserved words (images, auth, etc.)
      if (['image', 'auth'].indexOf(id) != -1) return true;

      // filter existing ids
      return db.users.count({
        readableId: id
      }) > 0;
    };

  var firstId = function (ids) {
      for (var i = 0; i < ids.length; i++)
      if (!idExists(ids[i])) return ids[i];
      return null;
    };

  var firstName = '%firstName%';
  var lastName = '%lastName%';

  var ids = [
    // "misha"
    formatId(firstName),

    // "mishay"
    formatId(firstName + lastName.substr(0, 1)),

    // "mishayurasov"
    formatId(firstName + lastName),

    // "myurasov"
    formatId(firstName.substr(0, 1) + lastName)
  ];

  var id = firstId(ids);

  // "misha##"
  if (id == null) {
    // try to create id with random 2-digit postfix
    var i = 0;

    do {
      id = formatId(firstName + Math.round(Math.random() * 99));
    } while (idExists(id) && ++i < 50)

    // failure, iterate postfix until match not found
    if (i == 50) {
      i = 100;

      do {
        id = formatId(firstName + i++);
      } while (idExists(id))
    }
  }

  return id;
})();