
app.controller('lotterynyController', ['$scope', '$filter', '$interval', '$rootScope', '$http',
    function ($scope, $filter, $interval, $rootScope, $http) {

        $scope.winners = [];

        $scope.popup = {

            flag: false,

            open: function () {
                this.flag = true;
            },

            close: function () {
                this.flag = false;
            },

            status: function () {
                return this.flag;
            }

        };
        $scope.popup_info = {

            flag: false,

            open: function () {
                this.flag = true;
            },

            close: function () {
                this.flag = false;
            },

            status: function () {
                return this.flag;
            }

        };

        if (( new Date() ).getMonth() != 11) {
            $scope.today = 0;
            $scope.firstSlide = 0;
        } else {
            $scope.today = ( new Date ).getDate();
            $scope.firstSlide = $scope.today - 1;
        }

        // if( $scope.model.route.host == 'localhost' ) {
        //     $scope.today = 23;
        // }

        $scope.setTimer = function () {
            var timer = ( new Date(( new Date() ).getFullYear(), ( new Date() ).getMonth(), ( new Date() ).getDate() + 1) ) - Date.now();
            timer = timer - ( ( new Date ).getTimezoneOffset() + 180 ) * 60 * 1000;
            return ( ( $filter('countdown')(timer) ).split(' ') )[1];
        };

        //console.log($scope.today);

        //$scope.today = 25;

        $scope.timer = $scope.setTimer();
        $scope.time = $interval(function () {
            $scope.timer = $scope.setTimer();
        }, 1000);

        $scope.rewards = [
            {
                id: 16,
                day: '16',
                date: '16 декабря',
                img: '/engine/templates/chudoslot/img/lottery/win_item_1.jpg',
                imgUnknown: '/engine/templates/chudoslot/img/lottery/undefined.jpg',
                bonusImg: '/engine/templates/chudoslot/img/lottery/bonus_1.png',
                name: 'Великий Устюг, Гостевой дом Северное Сияние',
                title: 'Поездка в Великий Устюг всей семьей',
                description: 'с проживанием в Гостевом Доме «Северное Сияние» для всей семьи, стоимость подарка — 122 504 рубля',
                bonusName: 'Пророческий бонус'
            },
            {
                id: 19,
                day: '19',
                date: '19 декабря',
                img: '/engine/templates/chudoslot/img/lottery/win_item_2.jpg',
                imgUnknown: '/engine/templates/chudoslot/img/lottery/undefined.jpg',
                bonusImg: '/engine/templates/chudoslot/img/lottery/bonus_2.png',
                name: 'Милан, Rosa Grand Milano - Starhotels Collezione',
                title: 'Поездка с семьей в Милан на Новый год на неделю',
                description: 'встретьте Новый год в отеле Rosa Grand Milano, Starhotels Collezione. Стоимость пакета — 213 648 рублей',
                bonusName: 'Зимнее  Бонусостояние'
            },
            {
                id: 22,
                day: '22',
                date: '22 декабря',
                img: '/engine/templates/chudoslot/img/lottery/win_item_3.jpg',
                imgUnknown: '/engine/templates/chudoslot/img/lottery/undefined.jpg',
                bonusImg: '/engine/templates/chudoslot/img/lottery/bonus_3.png',
                name: 'Париж, Molitor Paris - MGallery by Sofitel',
                title: 'Поездка с семьей в Париж на Новый год на неделю',
                description: 'с проживанием в отеле Molitor Paris, MGallery by Sofitel для семьи. Стоимость пакета — 360 218 рублей',
                bonusName: 'Бонусы от призраков Рождества'
            },
            {
                id: 25,
                day: '25',
                date: '25 декабря',
                img: '/engine/templates/chudoslot/img/lottery/win_item_4.jpg',
                imgUnknown: '/engine/templates/chudoslot/img/lottery/undefined.jpg',
                bonusImg: '/engine/templates/chudoslot/img/lottery/bonus_4.png',
                name: 'Нью-Йорк, The Peninsula New York',
                title: 'Поездка в Нью-Йорк с семьей на Рождество на неделю',
                description: 'с проживанием в отеле The Peninsula New York, стоимость пакета — 668 034 рубля',
                bonusName: 'Новый год в Простоквашино'
            },
            {
                id: 28,
                day: '28',
                date: '28 декабря',
                img: '/engine/templates/chudoslot/img/lottery/win_item_5.jpg',
                imgUnknown: '/engine/templates/chudoslot/img/lottery/undefined.jpg',
                bonusImg: '/engine/templates/chudoslot/img/lottery/bonus_5.png',
                name: 'Москва, Malliott Kudrinskaya Skyscraper',
                title: 'Поездка с семьей на Новый год в Москву',
                description: 'для всей семьи с проживанием в отеле Malliott Kudrinskaya Skyscraper, стоимость пакета — 248 927 рублей',
                bonusName: 'Новогодний рог изобилия'
            },
            {
                id: 31,
                day: '31',
                date: '31 декабря',
                img: '/engine/templates/chudoslot/img/lottery/win_item_6.jpg',
                imgUnknown: '/engine/templates/chudoslot/img/lottery/undefined.jpg',
                bonusImg: '/engine/templates/chudoslot/img/lottery/bonus_6.png',
                name: 'Санкт-Петербург, Петр Отель',
                title: 'Поездка с семьей в Санкт-Петербург на Рождество на неделю',
                description: 'проведите Рождество в шикарном «Петръ-отель». Стоимость пакета — 159 200 рублей',
                bonusName: 'Елочка, гори!'
            }
        ];

        $scope.todayItem = $scope.rewards.filter(function (item) {
            return item.id == $scope.today;
        })[0];

        $scope.dayFilter = function (item) {
            return item.id < $scope.today;
        };

        $scope.setCurObj = function (item, func) {
            func.open('winItemPopup');
            $scope.curObj = item;
        };

        $http.get('/engine/ajax/winners.php').then(
            function (answer) {
                $scope.winners = answer.data;
            },
            function (answer) {
            }
        );
    }]);