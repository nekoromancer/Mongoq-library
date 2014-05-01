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

입력하려고 하는 collection에 동일한 id의 Document가 존재하는 경우  
insert()를 이용해 입력할 경우 에러를 발생시키며,
save()로 입력시 해당 id의 Document가 새로 입력된 내용으로 변경됩니다. 

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
$data = array( '_id' => new MongoId( '52b6e9f94a6befa37cfe50da' ),
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

만일 동일 id의 Document가 있다면 지금 입력한 내용으로 교체될 것입니다.  id가 없다면 현재 입력된 id로 새로운 Document가 생성됩니다.  참고로 id 중복시 Document 전체가 교체되는 현상은 update시 set을 지정하지 않았을 때도 동일하게 발생합니다.

## j. update()
조건에 맞는 Document를 수정합니다. 조건은 where() 등을 이용해 부여합니다.  update()에는 두가지 매개변수를 옵션으로 부여할 수 있습니다.  

첫번째 옵션은 upsert입니다.  이 옵션이 true일 경우 where() 등을 이용해 부여한 조건에 일치하는 Document가 없다면 Insert를 수행하게 됩니다.  기본값은 false 입니다.

```php
$this->mongoq->update( true ); // upsert 옵션이 true로 지정됩니다.
```

두번째 옵션은 multiple입니다.  이 옵션이 true일 경우 조건에 일치하는 모든 Document를 변경할 것입니다.  false일 경우 조건에 일치하는 최초의 Document 한개만 수정할 것입니다.  기본값은 false입니다.  

```php
$this->mongoq->update( false, true ); // upsert 옵션은 false, multiple 옵션은 true 입니다.
```

update()는 수정할 사항을 아래의 함수등을 이용해 먼저 지정해 주어야 합니다.


### 1) set()
지정된 필드의 값을 변경합니다.

```php
$this->mongoq->collection('collection');
$this->mongoq->where('name', '=', 'neko'); // update를 실행할 조건은 name = neko 입니다.
$this->mongoq->set( 'name', 'inu' ); // name 필드의 값을 inu로 변경합니다.
$this->mongoq->update();
```

여러 값을 지정할 경우 배열을 사용할 수 있습니다.
```php
$set = array( 'name' => 'inu', 'age' => '30' );
$this->mongoq->set( $set );
```

### 2) unsetField()
해당 필드를 삭제합니다.

```php
$this->mongoq->unsetField('gender');
```

### 3) inc()
해당 필드를 지정한 숫자만큼 증가시킵니다.

```php
$this->mongoq->inc( 'item', 10 );
```

### 4) setOnInsert()
해당하는 필드가 존재하지 않을 때만 그 Document에 지정한 값을 입력합니다.  이 함수를 사용할 경우 upsert 옵션이 자동으로 true로 지정됩니다.

```
{
  _id : 100,
  product : 'doll',
  price : 30
}
```
위와 같은 document가 있다고 가정했을 때, 다음을 수행한 경우

```php
$this->mongoq->setOnInsert( 'qty', 100 );
$this->mongoq->update();
```
Document에 qty 라는 필드가 없으므로 Document에는 qty : 100 이라는 값이 추가 될 것입니다.

```
{
  _id : 100,
  product : 'doll',
  price : 30,
  qty : 100
}
```
이렇게 수정된 상태에서 setOnInsert를 이용해 다시 한번 다음과 같은 작업을 수행할 경우

```php
$this->mongoq->setOnInsert( 'qty', 200 );
$this->mongoq->update();
```
이미 Document에 qty라는 필드가 존재하므로 이 update 작업은 수행되지 않습니다.

### 5) now()
지정한 필드에 현재 시간을 입력합니다.

```php
$this->mongoq->now( 'mod_time' ); // mod_time 이라는 필드에 MongoDate 형식으로 현재 날짜와 시간이 입력됩니다.
$this->mongoq->update();
```

### 6) rename()
필드의 이름을 변경합니다.  현재 이름, 바꿀 이름 순으로 매개변수를 부여합니다.

```php
$this->mongoq->rename( 'nmae', 'name' ); // nmae라는 필드명을 name로 변경합니다.
$this->mongoq->update();
```

### 7) 배열 다루기
Mongo DB에서는 Document가 배열을 갖을 수 있습니다.  MongoQ에서는 배열을 수정하기 위한 몇가지 메소드를 제공합니다. PHP나 Javascript 등 다른 언어에서 배열을 다룬적이 있다면 쉽게 접근하실 수 있습니다.

#### 7-1) push()
지정한 필드에 배열을 값을 추가합니다.  예를 들어 다음과 같은 Document가 있다고 가정하고
```
{
  tags : [ 'neko', 'inu' ]
}
```

여기에 'kitune' 라는 항목을 추가 한다면

```php
$this->mongoq->push( 'tags', 'kitune' );
$this->mongoq->update();
```

결과는 다음과 같습니다.

```
{
  tags : [ 'neko', 'inu', 'kitune' ]
}
```

push()의 두번째 매개변수가 배열인 경우 모든 항목을 추가합니다.  위 Documnet를 그대로 사용합니다.

```php
$this->mongoq->push( 'tags', array( 'tanuki', 'oukami', 'tora' ) );
$this->mongoq->update();
```

결과는 다음과 같습니다.

```
{
  tags : [ 'neko', 'inu', 'kitune', 'tanuki', 'oukami', 'tora' ]
}
```

push()의 세번째 매개변수는 $slice라는 조금은 생소한 녀석으로 0 또는 음의 정수만을 갖을 수 있습니다(Mongo DB 2.6 이상에서는 양의 정수로 받을 수 있게 변경되었지만 아직 PHP에는 반영되지 않았습니다).  

$slice는 일단 두번째 매개변수에서 받은 배열($slice 사용시 두번째 매개변수는 반드시 배열이어야 합니다)을 필드에 추가한 다음 그 필드의 배열 항목을 $slice 값만큼의 뒷 부분만 남겨놓고 앞 부분은 '잘라서' 버립니다.

현재 6개 항목이 있는 tags 필드에 2개의 값을 더 추가하고 $slice 값은 -5를 주겠습니다.

```php
$this->mongoq->push( 'tags', array( 'sisi', 'mogura' ), -5 );
$this->mongoq->update();
```

```
{
  tags : [ 'tanuki', 'oukami', 'tora', 'sisi', 'mogura' ]
}
```
배열에 두개 항목이 추가된 후 뒤의 다섯 항목만 남고 앞부분은 잘려나갔습니다.

만일 두번째 매개변수에 빈 배열을 넣고, 세번째 매개변수인 $slice에 0을 넣게 되면 배열은 텅 비게 됩니다.

```php
$this->mongoq->push( 'tags', array(), 0 );
$this->mongoq->update();
```
```
{
  tags : []
}
```

#### 7-2) pull()
pull은 배열은 삭제하는 방법 중 하나입니다.  첫번째 매개변수는 필드명, 두번째 매개변수는 삭제할 값입니다.  아까의 Document를 다시 가져와 보겠습니다.

```
{
  tags : [ 'tanuki', 'oukami', 'tora', 'sisi', 'mogura' ]
}
```

여기서 'tanuki' 값을 빼고 싶습니다.

```php
$this->mongoq->pull( 'tags', 'tanuki' );
$this->mongoq->update();
```
```
{
  tags : [ 'oukami', 'tora', 'sisi', 'mogura' ]
}
```

두번째 매개변수에 배열을 주어보도록 하겠습니다.

```php
$this->mongoq->pull( 'tags', array( 'tora', 'sisi' ) );
$this->mongoq->update();
```
```
{
  tags : [ 'oukami', 'mogura' ]
}
```

배열과 일치하는 항목이 삭제되었습니다.

#### 7-3) addToSet()
addToSet()은 push와 유사한 기능을 합니다.  다만 addToSet() 동일한 값이 있으면 배열에 그 값은 기록하지 않고 새로운 값만 집어 넣습니다.

```
{
  tags : [ 1, 2, 3, 4 ,5 ]
}
```
```php
$this->mongoq->addToSet( 'tags', array( 3, 5, 6, 7 ) );
$this->mongoq->update();
```

결과는 중복 값인 3과 5를 제외한 6과 7이 추가됩니다.
```
{
  tags : [ 1, 2, 3, 4 ,5, 6, 7 ]
}
```
#### 7-4) pop()
보통 배열을 다른 언어에서와의 pop()이 하는 역할과 마찬가지로 배열의 마지막 값을 삭제합니다.

```
{
  tags : [ 1, 2, 3, 4 ,5, 6, 7 ]
}
```
```php
$this->mongoq->pop( 'tags' );
$this->mongoq->update();
```
이 코드의 결과로 7이 삭제 됩니다. 

```
{
  tags : [ 1, 2, 3, 4 ,5, 6 ]
}
```

pop의 두번째 매개변수에 -1를 주게 되면 이번에는 배열의 가장 앞의 값을 삭제합니다. 본래 shift가 하던 일이지만요.

```php
$this->mongoq->pop( 'tags', -1 );
$this->mongoq->update();
```
```
{
  tags : [ 2, 3, 4 ,5, 6 ]
}
```

## k. remove()
조건에 맞는 document를 삭제합니다. remove() 함수는 기본적으로 조건에 부합하는 Document 1개를 삭제합니다. 만일 조건에 맞는 모든 Document를 삭제하길 원한다면 옵션으로 false를 줍니다.

```php
$this->mongoq->collection('collection');
$this->mongoq->where('age', '<', 18);
$this->mongoq->remove(false); // age필드가 18 미만인 Document 전체를 삭제합니다.
```

## l. count()
count()의 사용법은 find() 혹은 get()과 거의 동일합니다.  find() 나 get()이 collection에서 조건에 맞는 Document를 인출하는 함수라면 count()는 collection에서 조건에 맞는 Document의 '수'를 반환합니다.

```php
$where = array( array( 'age', '<', 18 ),
                array( 'gender', '=', 'Female') );

$this->mongoq->collection('collection');
$this->mongoq->where( $where );

$result = $this->mongoq->count();
```

이렇게 콜랙션에서 age가 18세 미만이고 성별이 여성인 Document의 숫자를 파악할 수 있습니다.

## m. createCollection()
콜랙션을 생성합니다.  Mongo DB에서는 기본적으로 사용할 Collection을 선언하는 것 만으로도 자동적으로 Collection이 생성되므로 일반적인 경우에서는 명시적으로 Collection을 생성하여 사용하지 않습니다.

다만, 특별한 옵션을 가진 Collection을 생성해야 할 필요가 있다면 createCollection()을 이용하여 Collection을 생성할 필요가 있습니다.

기본적인 사용법의 예시는 다음과 같습니다.

```php
$options = array( 
                  'capped' => true,
                  'max' => 65536
                  );

$this->mongoq->createCollection('collectionName', $options);
```

createCollection()의 첫번째 매개변수는 생성한 Collection의 이름입니다.
두번째 매개변수는 Option으로 반드시 배열로 받습니다.  배열의 형태는 '옵션명' => 옵션값 의 형태입니다.

사용가능한 옵션은 다음과 같습니다.

```
capped : ( true/false ) 

Capped가 true로 설정된 Collection은 반드시 size나 max 옵션이 함께 부여되어야 합니다.  Capped collection은 
document의 용량 혹은 수가 지정한 size나 max 값을 넘어가게 되면 과거 Document부터 순차적으로 삭제하는 
Collection 입니다.
```
```
autoIndexId : ( true/false ) 

true인 경우 _id 필드에 해싱된 인덱스 값이 자동으로 생성됩니다.  일반 Collection에서는 기본적으로 true이며, 
Mongo DB 2.2 이상에서는 Capped Collection의 경우에도 기본값이 true 입니다.
```
```
size : (숫자) 

Capped Collection에서 Collection의 최대 사이즈를 지정합니다(Byte). 지정된 Size를 초과할 경우 과거 Document부터 
자동으로 삭제될 것입니다.  Capped Collection 이 아닌 경우 Size를 지정하게 되면 명시된 용량만큼의 DB 공간을 미리 
확보해 둡니다.
```
```
max : (숫자) 

Capped Collection에서 Collection에 담을 수 있는 최대 Document 수를 지정합니다. 지정된 수를 초과할 경우 과거 
Document부터 자동으로 삭제될 것입니다.
```
## n. dropCollection()
콜랙션을 삭제합니다.

```php
$this->mongoq->dropCollection( '콜렉션 이름' ); 
```

이 작업은 돌이킬 수 없습니다! 신중하게 결정하세요!

## o. dropCurrentDatabase()
현재 지정된 데이터 베이스를 삭제합니다.

```php
$this->mongoq->dropCurrentDatabase();
```

떠나간 데이터베이스는 돌아오지 않습니다.

## p. switchDB()
config/mongo.php 에서 설정한 기본 DB 외의 다른 DB에 접근하고자 할 때 사용합니다.
```php
$this->mongoq->switchDB( 'dog' ); // 지금부터는 dog 라는 이름의 데이터 베이스를 사용합니다.
```

## q. ensureIndex()
사용자가 임의로 Index값을 지정합니다.  serial_no 라는 필드가 있고 이 필드를 인덱스값으로 지정한다고 하면

```php
$this->mongoq->ensureIndex( array( 'serial_no' => 'asc' ), false );
```

첫번째 serial_no => asc 는 이 인덱스를 오름차순으로 생성한다는 의미입니다.
두번째 bool 값은 이 인덱스가 유일값을 갖는 인덱스인지 아닌지를 정하는 것으라 기본값은 false 입니다.
적절한 인덱스 설정은 데이터 베이스의 검색 속도를 비약적으로 상승시키지만 데이터 베이스 자체의 용량을 크게 만드는 요인이 되기 때문에 DB 설계시에 주의가 필요합니다. 

## r. group()
group() 메서드는 MongoDB의 기초적인 집계 연산인 group 연산을 수행합니다.  SQL의 GROUP BY와 유사한 부분이 많지만 Javascript 코드 문법을 활용하여 좀더 정교하고 다양한 조작이 가능합니다.  

MongoDB 콘솔에서는 initial 옵션을 생략하고 단순한 GROUP BY 연산을 실행하는 것이 가능합니다만, PHP에서는 initial 옵션의 생략을 허용하지 않기 때문에 반드시 하나 이상의 필드에 집계값을 도출해 내야 합니다.

group()의 메서드는 배열 형태의 매개변수를 갖습니다.  이 배열에는 Key => Value 형태로 옵션을 부여하여 사용가능한 옵션은 다음과 같습니다.

- keyf(필수) 
- reduce(필수) 
- initial(필수)
- finalize(옵션)
- cond(옵션)

## s. aggregation - 집계연산
### 1) addAggregationOpt()
### 2) getAggregation()
## t. setWoptions()
woptions 는 MongoDB에서 가장 변화무쌍한 옵션입니다.  버전이 하나 바뀔때마다 옵션이 사라지기도하고 새로운 옵션이 생기기도 하기 때문에 옵션에 대한 정확한 명세를 확인하기 위해서는 http://mongodb.org 에서 레퍼런스를 항상 확인 하는 것이 좋습니다.

이 옵션의 역할은 insert(), remove(), update() 등의 연산을 할 때 동기적으로 처리할 것인가 비동기적으로 처리할 것인가, 데이터의 입출력이 성공했는지 실패했는지 감시 할 것인가, timeout은 얼마나 할 것인가 등에 대한 처리가 이루어 집니다.
