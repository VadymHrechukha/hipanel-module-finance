<?php

namespace hipanel\modules\finance\widgets;

use DateTime;
use hipanel\assets\BootstrapDatetimepickerAsset;
use hipanel\modules\finance\models\proxy\Resource;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;

class ResourceListViewer extends BaseResourceViewer
{
    public function run(): string
    {
        $this->registerJs();
        $this->registerCss();

        return $this->render('ResourceListViewer', [
            'dataProvider' => $this->dataProvider,
            'originalContext' => $this->originalContext,
            'originalSearchModel' => $this->originalSearchModel,
            'uiModel' => $this->uiModel,
            'configurator' => $this->configurator,
            'model' => new Resource(),
        ]);
    }

    private function registerJs(): void
    {
        BootstrapDatetimepickerAsset::register($this->view);
        $request = Yii::$app->request;
        $ids = Json::encode(ArrayHelper::getColumn($this->dataProvider->getModels(), 'id'));
        $csrf_param = $request->csrfParam;
        $csrf_token = $request->csrfToken;
        $locale = Yii::$app->language;
        $cookieName = 'resource_date_' . $this->originalContext->id;
        $cookieOptions = Json::encode([
            'path' => '/' . $request->getPathInfo(),
            'expires' => (new DateTime())->modify('+45 minutes')->format(DateTime::RFC850),
            'max-age' => 3600,
            'samesite' => 'lax',
        ]);
        $this->view->registerJs(/** @lang JavaScript */ <<<"JS"
(() => {
  const ids = {$ids};
  const buildRange = momentObj => {
    return {
      time_from: momentObj ? momentObj.startOf('month').format('YYYY-MM-DD') : '',
      time_till: momentObj ? momentObj.endOf('month').format('YYYY-MM-DD') : ''
    }
  }
  const getDate = () => {
    const rawDate = getCookie('{$cookieName}');
    
    return rawDate ? moment(rawDate) : moment();
  }
  const setDate = momentObj => {
    setCookie('{$cookieName}', momentObj, {$cookieOptions});
  }
  const dateInput = $('input[name="date-range"]');
  const {time_from, time_till} = buildRange(getDate());
  dateInput.datetimepicker({
    date: getDate(),
    maxDate: moment(),
    locale: '{$locale}',
    viewMode: 'months',
    format: 'MMMM YYYY'
  });
  dateInput.datetimepicker().on('dp.update', evt => {
    $('td[data-type]').html('<div class="spinner"><div class="rect1"></div><div class="rect2"></div><div class="rect3"></div><div class="rect4"></div><div class="rect5"></div></div>');
    const date = evt.viewDate;
    setDate(date);
    fetchResources(ids, date.startOf('month').format('YYYY-MM-DD'), date.endOf('month').format('YYYY-MM-DD')).catch(err => {
      console.log(err);
      hipanel.notify.error(err.message);
    });
  });
  
  const fetchResources = async (ids, time_from, time_till) =>  {
    const formData = new FormData();
    formData.append('object_ids', ids);
    if (time_from.length !== 0 && time_till.length !== 0) {
      formData.append('time_from', time_from);
      formData.append('time_till', time_till);
    }
    formData.append('{$csrf_param}', '{$csrf_token}');
    
    try {
      const response = await fetch('{$this->fetchResourcesUrl}', {
        method: 'POST',
        body: formData
      });
      const result = await response.json();
      Object.entries(result).forEach(entry => {
        const [id, resources] = entry;
        Object.entries(resources).forEach(resource => {
          const [type, data] = resource;
          const cell = document.querySelector('tr[data-key="' + id + '"] > td[data-type="' + type + '"]');
          if (!!cell) {
            cell.innerHTML = data.amount;
          }
        });
      });
      const not_counted = document.createElement('span');
      not_counted.classList.add('text-danger');
      not_counted.appendChild(document.createTextNode('not counted'));
      document.querySelectorAll('tr[data-key] .spinner').forEach(node => {
        node.parentNode.replaceChild(not_counted.cloneNode(true), node);
      })
    } catch (error) {
      hipanel.notify.error(error.message);
    }
  }
  
  fetchResources(ids, time_from, time_till); // run request
  
  // Cookies
  function getCookie(name) {
    let matches = document.cookie.match(new RegExp(
      "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : undefined;
  }
  function setCookie(name, value, options = {}) {
    options = {
      ...options
    };
    if (options.expires instanceof Date) {
      options.expires = options.expires.toUTCString();
    }
    let updatedCookie = encodeURIComponent(name) + "=" + encodeURIComponent(value);
    for (let optionKey in options) {
      updatedCookie += "; " + optionKey;
      let optionValue = options[optionKey];
      if (optionValue !== true) {
        updatedCookie += "=" + optionValue;
      }
    }
    document.cookie = updatedCookie;
  }
  function deleteCookie(name) {
    setCookie(name, "", {
      'max-age': -1
    })
  }
})();
JS
            , View::POS_READY);
    }

    private function registerCss()
    {
        $this->view->registerCss(<<<CSS
.spinner {
  width: 50px;
  height: 10px;
  text-align: center;
  font-size: 10px;
  display: inline-block;
}

.spinner > div {
  background-color: #b8c7ce;
  height: 100%;
  width: 6px;
  display: inline-block;
  margin-right: .1rem;
  
  -webkit-animation: sk-stretchdelay 1.2s infinite ease-in-out;
  animation: sk-stretchdelay 1.2s infinite ease-in-out;
}

.spinner .rect2 {
  -webkit-animation-delay: -1.1s;
  animation-delay: -1.1s;
}

.spinner .rect3 {
  -webkit-animation-delay: -1.0s;
  animation-delay: -1.0s;
}

.spinner .rect4 {
  -webkit-animation-delay: -0.9s;
  animation-delay: -0.9s;
}

.spinner .rect5 {
  -webkit-animation-delay: -0.8s;
  animation-delay: -0.8s;
}

@-webkit-keyframes sk-stretchdelay {
  0%, 40%, 100% { -webkit-transform: scaleY(0.4) }
  20% { -webkit-transform: scaleY(1.0) }
}

@keyframes sk-stretchdelay {
  0%, 40%, 100% { 
    transform: scaleY(0.4);
    -webkit-transform: scaleY(0.4);
  }  20% { 
    transform: scaleY(1.0);
    -webkit-transform: scaleY(1.0);
  }
}
CSS
        );
    }
}