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

select() 함수에서는 두번째 매개변수를 통해 특정 필드를 제외하고 데이터를 인출할 수 있습니다.  Document에 name, age, address라는 필드가 있다고 가정하고 age를 제외한 전체 필드를 인출한다면, 

```php
$this->mongoq->select( array( 'age' ), true );
$this->mongoq->from('collection');

$result = $this->mongoq->get();
```
