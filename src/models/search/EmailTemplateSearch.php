<?php

namespace phuong17889\email\models\search;

use phuong17889\email\models\EmailTemplate;
use yii\data\ActiveDataProvider;

class EmailTemplateSearch extends EmailTemplate
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['id'],
                'integer',
            ],
            [
                [
                    'subject',
                    'shortcut',
                    'language',
                    'from',
                ],
                'safe',
            ],
        ];
    }

    /**
     * @return array
     */
    public function scenarios()
    {
        return parent::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search(array $params)
    {
        $query = EmailTemplate::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'language' => $this->language,
            'from' => $this->from,
        ]);
        $query->andFilterWhere([
            'like',
            'subject',
            $this->subject,
        ])->andFilterWhere([
            'like',
            'shortcut',
            $this->shortcut,
        ]);
        return $dataProvider;
    }
}
