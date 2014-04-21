Mongoq-library
==============

MongoDB library for php framework Codeigniter

MongoQ는 PHP 프레임워크인 Codeigniter의 MongoDB 라이브러리입니다.

# 0. 설치
라이브러리 본체인 Mongoq.php는 application/library 디렉토리에 복사합니다.
설정파일인 mongo.php는 application/config 디렉토리에 복사합니다.

# 1. 설정하기
application/config 에 복사한 mongo.php 파일을 열게되면 다음과 같은 내용을 확인할 수 있습니다.

```php
$config["username"] = "Enter your MongoDB Username";
$config["password"] = "Enter your MongoDB password";
$config["hostname"] = "localhost"; // default : localhost
$config["port"] = "27017"; // default : 27017
$config["dbname"] = "Enter your Database name for use";
```

몇가지 항목이 더 있지만 필수적으로 설정해야 할 사항은 위 다섯가지 입니다.  나머지는 기본값으로 두셔도 무방하며 필요에 따라 설정을 변경하시면 됩니다.

$config["username"] 사용하실 DB에 설정된 User명을 입력합니다.
$config["password"] 사용하실 DB에 설정된 Password를 입력합니다.
$config["hostname"] 사용하실 DB가 설치된 호스트명을 입력합니다.  DB가 현재 서버에 설치되어 있다면 기본값인 'localhost' 로 두시면 되겠습니다.
$config["port"] 사용하실 DB에 접속하기 위한 port 번호를 입력합니다.  특별한 설정의 변경없이 설치하셨다면 27017번이 사용되고 있을 것입니다.
$config["dbname"] 사용하실 DB의 이름을 입력합니다.  여기에 사용된 DB 이름은 라이브러리를 초기화 할 때 기본 DB로 설정합니다.  물론 라이브러리의 함수를 이용하여 다른 DB를 호출할 수도 있습니다.

# 2. 라이브러리 호출
소스에서 다음과 같이 라이브러리를 호출합니다.

```php
$this->load->library('mongoq');
```

php에 MongoDB 드라이버가 설치되지 않았다면 에러가 발생하게 되니 주의하시기 바랍니다.

# 3. 메서드
MongoQ는 기본적으로 CI의 액티브 레코드와 유사한 형태로 동작하도록 작성되었습니다.

## a. find()
DB에서 Document를 인출하는 함수입니다.  기본적인 사용법은 다음과 같습니다. 반환값은 MongoDB Cursor Object 형태로 반환됩니다.

```php
$this->mongoq->collection('collection_name');

$result = $this->mongoq->find();
```

MongoDB에서 collection은 sql에서 Table에 해당합니다.  Mongoq의 대부분의 함수는 collection() 함수를 이용하여 collection을 지정하지 않으면 에러가 발생하니 주의하시기 바랍니다.  collection 지정은 from()을 사용할 수도 있습니다.  두 함수는 완전히 동일하게 동작기 때문에 취향에 맞게 사용하시면 됩니다.

```php
$this->mongoq->from('collection_name');

$result = $this->mongoq->find();
```

또한 다음과 같이 메서드 체인의 형태로 사용할 수도 있습니다.

```php
$result = $this->mongoq->from('collection_name')->find();
```

CI의 액티브 레코드와 마찬가지로 find() 대신 get()을 사용할 수 있으며 이 역시 완전히 동일하게 동작합니다.

```php
$result = $this->mongoq->from('collection_name')->get();
```

반환되는 결과값을 곧바로 배열 형태로 받을 수도 있습니다.  이 때는 find()나 get()의 매개변수로 true를 사용하면 됩니다.

```php
$result = $this->mongoq->from('collection_name')->get( true );
```

## b. select()
sql에서 SELECT 구문에 해당합니다.  select()를 통해 필드를 지정하지 않고 find()나 get()을 통해 데이터를 인출할 경우 sql에서 다음의 쿼리문과 같이 동작합니다.

```
SELECT      *
FROM        table_name
```

DB에 저장된 Document에 name과 age라는 필드가 있다고 가정하고 select() 함수를 이용해 필드를 지정해 보겠습니다.  이 때 매개변수는 배열의 형태로 입력 받습니다.

```php
$this->mongoq->select( array( 'name', 'age') );
$this->mongoq->from('collection');

$result = $this->mongoq->get();
```

이 코드는 다음 sql 문에 해답니다.
```
SELET      name, age
FROM       collection;
```

select() 함수에서는 두번째 매개변수를 통해 특정 필드를 제외하고 데이터를 인출할 수 있습니다.  Document에 name, age, address라는 필드가 있다고 가정합니다.

```php
$this->mongoq->select( array( 'age' ), fasle );
$this->mongoq->from('collection');

$result = $this->mongoq->get();
```
이 코드는 age 필드를 제외한 전체 필드를 인출할 것입니다.
 
## c. where()
sql에서 WHERE 구문에 해당합니다.  다수의 조건을 할당할 경우 where() 함수는 부여된 조건문들을 and 로 묶습니다.  다른 논리연산자로 조건을 묶기 위해서는 별도의 함수가 마련되어 있습니다.

where()를 이용해 조건을 부여하는 방법에는 두 가지가 마련되어 있습니다.  첫번째는 한번에 하나의 조건을 입력하는 것입니다.

```php
$this->mongoq->from('collection');
$this->mongoq->where( 'name', '=', 'neko' );

$result = $this->mongoq->get();
```
조건문의 입력순서는 필드명, 조건연산자, 값입니다.  위 코드는 다음 sql 쿼리문에 해당합니다.

```
SELECT    *
FROM      collection
WHERE     name = 'neko';
```

다수의 조건을 부여하기 위해서는 2차원 배열을 사용합니다.

```php
$wheres = array(
                 array( 'name', '=', 'neko' ),
                 array( 'age', '>', 18 )
               );

$this->mongoq->from('collection');
$this->mongoq->where( $wheres );

$result = $this->mongoq->get();
```

위 코드는 name 필드가 neko이고 age가 18보다 큰 Document를 collection이라는 이름의 콜랙션에서 인출합니다.  sql 쿼리문으로 변경하면 다음과 같습니다.

```
SELECT    *
FROM      collection
WHERE     name = 'neko' AND age > 18;
```

조건을 부여하기 위한 비교연산자는 '=', '>', '<', '>=', '<=', '<>', '!=', 'in', 'not in', 'like' 입니다.  <> 와 != 연산자는 동일하게 작동합니다.  

like 연산자는 편의상 sql의 like 연산자처럼 작동하지만 본래 MongoDB 명세상 정규식을 활용한 조건을 부여합니다.  MongoQ 라이브러리에서는 /value/i 형태(대소문자 구분없이 value를 포함한 구문)의 정규식을 생성하여 입력하게 되며 이는 sql문의 like 연산자처럼 작동하게 됩니다.

## d. orWhere(), notWhere(), norWhere()
where()와 마찬가지로 조건문을 부여하기 위한 함수입니다.  이름에서 알 수 있듯이 각각의 조건을 orWhere()는 or로, notWhere()는 not으로, notWhere()는 nor로 묶습니다.  그외의 사용법은 where()와 동일합니다.

단, orWhere()의 경우 or 연산이 하나의 조건문에서는 의미를 갖지 않기 때문에 where()에서와 같이 배열을 사용하지 않는 조건문 부여 방식은 허용되지 않습니다.  orWhere()에서 배열을 사용하지 않는 구문을 입력할 경우 에러가 발생할 것입니다.

## e. sort()
인출된 데이터를 필드 기준으로 오름차순 혹은 내림차순으로 정렬합니다.

```
SELECT     *
FROM       collection
WHERE      age > 18
ORDER BY   name ASC;
```
age가 18 초과인 데이터를 name을 기준으로 오름차순 정렬합니다.  이를 MongoQ로 표현하면,

```php
$this->mongoq->from('collection');
$this->mongoq->where('age', '>', 18);
$this->mongoq->sotr(name, 'asc');

$result = $this->mongoq->get();
```

2개 이상의 조건이 필요할 경우에는 array를 사용하여 입력합니다.

```php
$this->mongoq->from('collection');
$this->mongoq->where('age', '>', 18);
$this->mongoq->sort(array('name' => 'desc', 'age' => 'asc'));

$result = $this->mongoq->get();
```

## f. limit(), skip(), offset()
limit()는 인출할 Document의 수를 지정하거나 최초 일정수의 Document를 제외하고 데이터를 인출합니다. 인출할 Document의 수와 관계없이 최초 일정수의 Document를 제외할 경우에는 skip() 혹은 offset() 사용하며, skip()과 offset()는 완전히 동일하게 동작합니다.

```
SELECT    *
FROM      collection
LIMIT     10;
```

위 sql 쿼리를 MongoQ로 표현하면 다음과 같습니다.

```php 
$this->mongoq->from('collection');
$this->mongoq->limit(10);

$result = $this->mongoq->get();
```

최초 5개의 Document 제외하고 이후 10개의 Document 를 가져온다고 했을때 이를 sql 쿼리로 표편하면,

```
SELECT    *
FROM      collection
LIMIT     5, 10;
```

이를 MongoQ 코드로 표현하면 다음과 같습니다.

```php
$this->mongoq->from('collections');
$this->mongoq->limit(5, 10);

$result = $this->mongoq->get();
```

또한 skip, offset을 이용해서 동일한 결과를 얻을 수 있습니다.
```php
$this->mongoq->from('collection');
$this->mongoq->skip(5); // $this->mongoq->offset(5) 같은 구문입니다.
$this->mongoq->limit(10);

$result = $this->mongoq->get();
```

## g. distinct()

collection에서 지정한 field의 유일값을 산출합니다.  
```
SELECT DISTINTCT   name
FROM               collection
WHERE              gender = 'male';
```

distinct()는 필드값은 매개변수를 통해 지정하며, 조건값은 where()을 이용합니다.  위의 sql문을 MongoQ 코드로 표현하면,

```php
$this->mongoq->from('collection');
$this->mongoq->where('gender', '=', 'male');

$result = $this->mongoq->distinct('name');
```

## h. insert()
php에서 document를 입력할 때, 우선 BSON( 혹은 JSON )형태의 데이터를 배열로 표현할 필요가 있습니다.

```
{
  "name" : "Neko",
  "age"  : 20,
  "language" : [ "KR", "JP" ]
}
```
먼저 위의 Document를 배열로 표현합니다.

```php
$data = array( 'name' => 'neko',
               'age' => 20,
               'language' => array( 'kr', 'jp' ) );
```
배열로 표현된 Document를 insert()함수를 통해 입력합니다.

```php
$this->mongoq->collection('collection');
$this->mongoq->insert($data);
```

Document가 DB에 입력되었습니다.

## i. save()
save()는 id값을 지정하지 않을 경우 insert()와 완전히 동일하게 동작합니다.  하지만 id값을 지정했을 때 그 차이가 발생하게 됩니다.

입력하려고 하는 collection에 동일한 id의 Document가 존재하는 경우,

insert()로 입력가 발생합니다.
save()로 입력시 해당 id에 ocument가 새로 입력된 내용으로 변경됩니다. 

따라서 새로운 Document를 입력할 시에는 insert()를 사용하며,
Document 단위로 데이터를 수정할 시에는 save()를 사용하고,
Field 단위로 데이터를 수정할 시에는 update()를 사용하는 것이 권장됩니다.

save()의 사용법은 insert()와 동일합니다.  먼저 데이터를 준비합니다. 이번에는 id를 지정해보겠습니다. 

```
{
  "_id"  : ObjectID( 52b6e9f94a6befa37cfe50da ),
  "name" : "Neko",
  "age"  : 20,
  "language" : [ "KR", "JP" ]
}
```

이를 배열로 표현하면 다음과 같습니다.

```php
$data = array( '_id' => new MongoId( 52b6e9f94a6befa37cfe50da ),
               'name' => 'neko',
               'age' => 20,
               'language' => array( 'kr', 'jp' ) );
```

MongoDB의 id 필드의 필드명이 '_id'인 것에 유의하세요.  또한 id값은 MongoId 객체이므로 new MongoId를 통해 생성하여 입력해 주여야 합니다.

그럼 위의 데이터를 save()를 통해 입력합니다.

```php
$this->mongoq->collection('collection');
$this->mongoq->save($data);
```

만일 동일 id의 Document가 있다면 지금 입력한 내용으로 교체될 것입니다.  id가 없다면 현재 입력된 id로 새로운 Document가 생성됩니다.  참고로 id 중복시 Document 전체가 교환된는 현상은 update시 set을 지정하지 않았을 때도 동일하게 발생합니다.
