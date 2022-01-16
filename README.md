
Η εργασία υλοποιήθηκε απο την : Μπαρμπαγιάννη Ελένη 185239

H προσωπική μου ιστοσελίδα : 

https://users.it.teithe.gr/~it185239/adise/www/nine_mens_morris.php

Παχνίδι που επιλέχτηκε : Nine men's Morris




#### Μεταβολή Θέσης Πιονιού

```
PUT /board/piece/:x/:y/
```
Json Data:

| Field             | Description                 | Required   |
| ----------------- | --------------------------- | ---------- |
| `x`               | Η νέα θέση x                | yes        |
| `y`               | Η νέα θέση y                | yes        |

Επιστρέφει τα στοιχεία από το [Board](#Board-1) με συντεταγμένες x,y.
Περιλαμβάνει το χρώμα του πιονιού και τον τύπο


### Player

#### Ανάγνωση στοιχείων παίκτη
```
GET /players/:p
```

Επιστρέφει τα στοιχεία του παίκτη p ή όλων των παικτών αν παραληφθεί. Το p μπορεί να είναι 'B' ή 'W'.

#### Καθορισμός στοιχείων παίκτη
```
PUT /players/:p
```
Json Data:

| Field             | Description                 | Required   |
| ----------------- | --------------------------- | ---------- |
| `username`        | Το username για τον παίκτη p. | yes        |
| `color`           | To χρώμα που επέλεξε ο παίκτης p. | yes        |


Επιστρέφει τα στοιχεία του παίκτη p και ένα token. Το token πρέπει να το χρησιμοποιεί ο παίκτης καθόλη τη διάρκεια του παιχνιδιού.

### Status

#### Ανάγνωση κατάστασης παιχνιδιού
```
GET /status/
```

Επιστρέφει το στοιχείο [Game_status](#Game_status).



## Entities


### Board
---------

Το board είναι ένας πίνακας, ο οποίος στο κάθε στοιχείο έχει τα παρακάτω:


| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | ----------------------------------- |
| `x`                      | H συντεταγμένη x του τετραγώνου              | 1..8                                |
| `y`                      | H συντεταγμένη y του τετραγώνου              | 1..8                                |
| `piece_color`            | To χρώμα του πιονιού                         | 'B','W', null                       |

### Players
---------

O κάθε παίκτης έχει τα παρακάτω στοιχεία:


| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | ----------------------------------- |
| `username`               | Όνομα παίκτη                                 | String                              |
| `piece_color`            | To χρώμα που παίζει ο παίκτης                | 'B','W'                             |
| `token  `                | To κρυφό token του παίκτη. Επιστρέφεται μόνο τη στιγμή της εισόδου του παίκτη στο παιχνίδι | HEX |


### Game_status
---------

H κατάσταση παιχνιδιού έχει τα παρακάτω στοιχεία:


| Attribute                | Description                                  | Values                              |
| ------------------------ | -------------------------------------------- | ----------------------------------- |
| `status  `               | Κατάσταση             | 'not active', 'initialized', 'started', 'ended', 'aborted'     |
| `p_turn`                 | To χρώμα του παίκτη που παίζει        | 'B','W',null                              |
| `result`                 |  To χρώμα του παίκτη που κέρδισε |'B','W',null                              |
| `last_change`            | Τελευταία αλλαγή/ενέργεια στην κατάσταση του παιχνιδιού         | timestamp |
| `b_setup`                |Παίρνει την τιμή 1 όταν ο παίκτης χρώματος black τοποθετήσει όλα τα πιόνια στο ταμπλό | 0,1|
|  `w_setup`               |Παίρνει την τιμή 1 όταν ο παίκτης χρώματος white τοποθετήσει όλα τα πιόνια στο ταμπλό | 0,1|
|  `b_delete`              |όταν παιρνει την τιμή 1 ,ο παικτης χρώματος black μπορει να αφαιρέσει ενα πιόνι του αντιπάλου | 0,1|
|  `w_delete`              |όταν παιρνει την τιμή 1 ,ο παικτης χρώματος white μπορει να αφαιρέσει ενα πιόνι του αντιπάλου| 0,1|
