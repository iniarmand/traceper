<?php

/**
 * This is the model class for table "traceper_upload".
 *
 * The followings are the available columns in table 'traceper_upload':
 * @property integer $Id
 * @property integer $fileType
 * @property string $userId
 * @property string $latitude
 * @property string $longitude
 * @property string $altitude
 * @property string $uploadTime
 * @property integer $publicData
 * @property string $description
 * @property integer $isLive
 * @property integer $liveKey
 *
 * The followings are the available model relations:
 * @property TraceperUsers $user
 */
class Upload extends CActiveRecord
{
    /**
     * Returns the static model of the specified AR class.
     * @return Upload the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'traceper_upload';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('fileType, userId, latitude, longitude, altitude, uploadTime, description, isLive, liveKey', 'required'),
            array('fileType, publicData, isLive, liveKey', 'numerical', 'integerOnly'=>true),
        	array('userId, longitude', 'length', 'max'=>11), //Condidering -180.000000 for latitude (11 digits)
        	array('latitude', 'length', 'max'=>10), //Condidering -90.000000 for latitude (10 digits)
            array('altitude', 'length', 'max'=>15),
            array('description', 'length', 'max'=>255),
            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('Id, fileType, userId, latitude, longitude, altitude, uploadTime, publicData, description, isLive, liveKey', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array(
            'user' => array(self::BELONGS_TO, 'Users', 'userId'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'Id' => 'ID',
            'fileType' => 'File Type',
            'userId' => 'User',
            'latitude' => 'Latitude',
            'longitude' => 'Longitude',
            'altitude' => 'Altitude',
            'uploadTime' => 'Upload Time',
            'publicData' => 'Public Data',
            'description' => 'Description',
            'isLive' => 'Is Live',
            'liveKey' => 'Live Key',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('Id',$this->Id);
        $criteria->compare('fileType',$this->fileType);
        $criteria->compare('userId',$this->userId,true);
        $criteria->compare('latitude',$this->latitude,true);
        $criteria->compare('longitude',$this->longitude,true);
        $criteria->compare('altitude',$this->altitude,true);
        $criteria->compare('uploadTime',$this->uploadTime,true);
        $criteria->compare('publicData',$this->publicData);
        $criteria->compare('description',$this->description,true);
        $criteria->compare('isLive',$this->isLive);
        $criteria->compare('liveKey',$this->liveKey);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
    
    public function getId($liveKey) 
    {
    	$upload =$this->find(array(
    				'select'=>'Id',
    				'condition'=>'liveKey=:liveKey',
   					'params'=>array(':liveKey'=>$liveKey),
				));
    	$result = null;
    	if ($upload != null) {
    		$result = $upload->Id;
    	}
    	return $result;
    }
    
    //Returns the Id of the last inserted upload
    public function addNewRecord($fileType,$userID,$latitude, $longitude, $altitude, $uploadTime, $publicData, $description, $isLive, $liveKey) 
    {
//     	$sql = sprintf('INSERT INTO '
//     			. Upload::model()->tableName() .'
//     			(fileType, userId, latitude, longitude, altitude, uploadtime, publicData, description, isLive, liveKey)
//     			VALUES(%d, %d, %s, %s, %s, NOW(), %d, "%s", %d, %d)',
//     			$fileType, $userID, $latitude, $longitude, $altitude, $publicData, $description, $isLive, $liveKey);
    	
    	$sql = sprintf('INSERT INTO '
    			. Upload::model()->tableName() .'
    			(fileType, userId, latitude, longitude, altitude, uploadtime, publicData, description, isLive, liveKey)
    			VALUES(%d, %d, %s, %s, %s, "%s", %d, "%s", %d, %d)',
    			$fileType, $userID, $latitude, $longitude, $altitude, $uploadTime, $publicData, $description, $isLive, $liveKey);    	
		
    	$effectedRows = Yii::app()->db->createCommand($sql)->execute();
    	
    	if($effectedRows > 0)
    	{
    		$uploadId = Yii::app()->db->getLastInsertId();	
    	}
    	else
    	{
    		$uploadId = 0;
    	}
    	
//     	$upload = new Upload();
    	
//     	$upload->fileType = $userID;
//     	$upload->userId = $fileType;
//     	$upload->latitude = $latitude;
//     	$upload->longitude = $longitude;
//     	$upload->altitude = $altitude;
//     	$upload->uploadtime = $uploadTime;
//     	$upload->publicData = $publicData;
//     	$upload->description = description;
//     	$upload->isLive = $isLive;
//     	$upload->liveKey = $liveKey;

    	
//     	return $upload->save();    	
    	   	
    	return $uploadId;
    }
    
    
    public function getRecordList($fileType,$userID,$friendList) {
    
    	//if the upload is mine or my friends' upload or public.
    	$sqlCount = 'SELECT count(*)
    	FROM '. Upload::model()->tableName() . ' u
    	WHERE (fileType = '.$fileType.') AND (userId in ('. $friendList .')
    	OR userId = '. $userID .' OR
    	publicData = 1)';

    	$count=Yii::app()->db->createCommand($sqlCount)->queryScalar();

		//$sqlCount = 'SELECT COUNT(*) FROM (SELECT 1 FROM '.Upload::model()->tableName() .' LIMIT 10) t';   	  	
    	//$count=Yii::app()->db->createCommand($sqlCount)->query()->readColumn(0);
    	
    	//Fb::warn($count, "count");
    
//     	$sql = 'SELECT u.Id as id, u.description, u.fileType, s.realname, s.Id as userId
//     	FROM '. Upload::model()->tableName() . ' u
//     	LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
//     	WHERE (fileType = '.$fileType.') AND (userId in ('. $friendList .') OR
//     	userId = '. $userID .' OR
//     	publicData = 1)
//     	ORDER BY u.Id DESC';
    	
    	$sql = 'SELECT u.Id as id, u.description, u.fileType, s.realname, s.Id as userId
    	FROM '. Upload::model()->tableName() . ' u
    	LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
    	WHERE (fileType = '.$fileType.') AND (userId in ('. $friendList .') OR
    	userId = '. $userID .' OR
    	publicData = 1)
    	ORDER BY userId = '. $userID .' DESC,
    	userId in ('. $friendList .') DESC,
    	publicData = 1 DESC';

    	if(isset(Yii::app()->session['uploadsPageSize']) == false)
    	{
    		Yii::app()->session['uploadsPageSize'] = Yii::app()->params->uploadCountInOnePage;
    	}    	

    	$dataProvider = new CSqlDataProvider($sql, array(
    			'totalItemCount'=>$count,
    			'sort'=>array(
    					'attributes'=>array(
    							'id',
    					),
    			),
    			'pagination'=>array(
    					'pageSize'=>Yii::app()->session['uploadsPageSize'],
    					'itemCount'=>$count
    			),
    	));
    
    	return $dataProvider;
    }
    
    public function getPublicRecordList($fileType) {
    
    	//if the upload is mine or my friends's upload or public.
    	$sqlCount = 'SELECT count(*)
    	FROM '. Upload::model()->tableName() . ' u
    	WHERE (fileType = '.$fileType.') AND (publicData = 1)';
    
    	$count=Yii::app()->db->createCommand($sqlCount)->queryScalar();
    
    	$sql = 'SELECT u.Id as id, u.description, u.fileType, s.realname, s.Id as userId
    	FROM '. Upload::model()->tableName() . ' u
    	LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
    	WHERE (fileType = '.$fileType.') AND (publicData = 1)
    	ORDER BY u.Id DESC';
    	
    	if(isset(Yii::app()->session['publicUploadsPageSize']) == false)
    	{
    		Yii::app()->session['publicUploadsPageSize'] = Yii::app()->params->uploadCountInOnePage;
    	}    	
       
    	$dataProvider = new CSqlDataProvider($sql, array(
    			'totalItemCount'=>$count,
    			'sort'=>array(
    					'attributes'=>array(
    							'id',
    					),
    			),
    			'pagination'=>array(
    					'pageSize'=>Yii::app()->session['publicUploadsPageSize'],
    					'itemCount'=>$count
    			),
    	));
    
    	return $dataProvider;
    }    
    
    
    public function getSearchResult($fileType,$userID,$friendList,$keyword,$keywordAttributes) {
    
    	//if the upload is mine (user upload) or my friends's upload or public.
    	//search criterias are the upload description or the upload owner's realname.
    	$sqlCount = 'SELECT count(*)
    	FROM '. Upload::model()->tableName() . ' u
    	LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
    	WHERE (fileType = '.$fileType.') AND (u.userId in ('. $friendList .')
    	OR
    	u.userId = '. $userID .'
    	OR
    	u.publicData = 1 )
    	AND
    	(s.realname like "%'. $keyword .'%"
    	OR
    	u.description like "%'. $keyword.'%")';
    
    	$count=Yii::app()->db->createCommand($sqlCount)->queryScalar();
    
    	$sql ='SELECT u.Id as id, u.description, u.fileType, s.realname, s.Id as userId
    	FROM '. Upload::model()->tableName() . ' u
    	LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
    	WHERE (fileType = '.$fileType.') AND (u.userId in ('. $friendList .')
    	OR
    	u.userId = '. $userID .'
    	OR
    	u.publicData = 1)
    	AND
    	(s.realname like "%'. $keyword .'%"
    	OR
    	u.description like "%'. $keyword.'%")';
    
    	$dataProvider = new CSqlDataProvider($sql, array(
    			'totalItemCount'=>$count,
    			'sort'=>array(
    					'attributes'=>array(
    							'id', 'realname',
    					),
    			),
    			'pagination'=>array(
    					'pageSize'=>Yii::app()->params->uploadCountInOnePage,
    					'params'=>array(CHtml::encode('SearchForm[keyword]')=>$keywordAttributes),
    			),
    	));
    
    	return $dataProvider;
    }
    
    public function getUploadCount($fileType,$userID,$friendList) {
    	$sqlCount = 'SELECT count(*)
    	FROM '. Upload::model()->tableName() . ' u
    	WHERE (fileType = '.$fileType.') AND (userId in ('. $friendList .') OR
    	userId = '. $userID .' OR
    	publicData = 1)';
    
    	$count = Yii::app()->db->createCommand($sqlCount)->queryScalar();
    	    
    	return $count;
    }    
    
    public function getUploadPageCount($fileType,$userID,$friendList,$time) {
    
    	if ($time != NULL)
    	{
    		$sqlCount = 'SELECT ceil(count(*)/'. Yii::app()->session['uploadsPageSize'] .')
    		FROM '. Upload::model()->tableName() . ' u
    		WHERE (fileType = '.$fileType.') AND (userId in ('. $friendList .')
    		OR userId = '. $userID .'
    		OR publicData = 1)
    		AND unix_timestamp(u.uploadTime) >= '. $time;
    	}
    	else
    	{
    		$sqlCount = 'SELECT ceil(count(*)/'. Yii::app()->session['uploadsPageSize'] .')
    		FROM '. Upload::model()->tableName() . ' u
    		WHERE (fileType = '.$fileType.') AND (userId in ('. $friendList .') OR
    		userId = '. $userID .' OR
    		publicData = 1)';
    	}
    
    	$pageCount=Yii::app()->db->createCommand($sqlCount)->queryScalar();
    	
    
    	return $pageCount;
    }
    
    public function getPublicUploadCount($fileType) {
    	$sqlCount = 'SELECT count(*)
    	FROM '. Upload::model()->tableName() . ' u
    	WHERE (fileType = '.$fileType.') AND (publicData = 1)';
    
    	$count = Yii::app()->db->createCommand($sqlCount)->queryScalar();
    		
    	return $count;
    }    
    
    public function getPublicUploadPageCount($fileType,$time) {
    
    	if ($time != NULL)
    	{
    		$sqlCount = 'SELECT ceil(count(*)/'. Yii::app()->session['publicUploadsPageSize'] .')
    		FROM '. Upload::model()->tableName() . ' u
    		WHERE (fileType = '.$fileType.') AND (publicData = 1)
    		AND unix_timestamp(u.uploadTime) >= '. $time;
    	}
    	else
    	{
    		$sqlCount = 'SELECT ceil(count(*)/'. Yii::app()->session['publicUploadsPageSize'] .')
    		FROM '. Upload::model()->tableName() . ' u
    		WHERE (fileType = '.$fileType.') AND (publicData = 1)';
    	}
    
    	$pageCount=Yii::app()->db->createCommand($sqlCount)->queryScalar();    	 
    
    	return $pageCount;
    }    
    
    public function getUploadList($fileType,$userID,$friendList,$time,$offset) {
    	if ($time != NULL)
    	{
    		$sql = 'SELECT u.Id as id, u.description, s.realname, s.Id as userId, date_format(u.uploadTime,"%d %b %Y %T") as uploadTime, u.altitude, u.latitude, u.longitude, u.publicData
    		FROM '. Upload::model()->tableName() . ' u
    		LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
    		WHERE (fileType = '.$fileType.') AND (userId in ('. $friendList .')
    		OR userId = '. $userID .'
    		OR publicData = 1)
    		AND unix_timestamp(u.uploadTime) >= '. $time . '
	    	ORDER BY userId = '. $userID .' DESC,
	    	userId in ('. $friendList .') DESC,
	    	publicData = 1 DESC     		
    		LIMIT '. $offset . ' , ' . Yii::app()->session['uploadsPageSize'] ;
    	}
    	else
    	{
    		$sql = 'SELECT u.Id as id, u.description, s.realname, s.Id as userId, date_format(u.uploadTime,"%d %b %Y %T") as uploadTime, u.altitude, u.latitude, u.longitude, u.publicData
    		FROM '. Upload::model()->tableName() . ' u
    		LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
    		WHERE (fileType = '.$fileType.') AND (userId in ('. $friendList .') OR
    		userId = '. $userID .' OR
    		publicData = 1)
	    	ORDER BY userId = '. $userID .' DESC,
	    	userId in ('. $friendList .') DESC,
	    	publicData = 1 DESC    		
    		LIMIT '. $offset . ' , ' . Yii::app()->session['uploadsPageSize'] ;
    	}
    
    	$dataReader = Yii::app()->db->createCommand($sql)->query();
    
    	return $dataReader;
    }
    
    public function getPublicUploadList($fileType,$time,$offset) {
    	if ($time != NULL)
    	{
    		$sql = 'SELECT u.Id as id, u.description, s.realname, s.Id as userId, date_format(u.uploadTime,"%d %b %Y %T") as uploadTime, u.altitude, u.latitude, u.longitude, u.publicData
    		FROM '. Upload::model()->tableName() . ' u
    		LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
    		WHERE (fileType = '.$fileType.') AND (publicData = 1)
    		AND unix_timestamp(u.uploadTime) >= '. $time . '
    		ORDER BY u.Id DESC
    		LIMIT '. $offset . ' , ' . Yii::app()->session['publicUploadsPageSize'] ;
    	}
    	else
    	{
    		$sql = 'SELECT u.Id as id, u.description, s.realname, s.Id as userId, date_format(u.uploadTime,"%d %b %Y %T") as uploadTime, u.altitude, u.latitude, u.longitude, u.publicData
    		FROM '. Upload::model()->tableName() . ' u
    		LEFT JOIN  '. Users::model()->tableName() . ' s ON s.Id = u.userId
    		WHERE (fileType = '.$fileType.') AND (publicData = 1)
    		ORDER BY u.Id DESC
    		LIMIT '. $offset . ' , ' . Yii::app()->session['publicUploadsPageSize'] ;
    	}
    
    	$dataReader = Yii::app()->db->createCommand($sql)->query();
    
    	return $dataReader;
    }    
}