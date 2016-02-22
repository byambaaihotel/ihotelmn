<?php
    require_once 'includes/Twig/Autoloader.php';
    require_once "config.php";
    use Parse\ParseObject;
    use Parse\ParseClient;
    use Parse\ParseQuery;
    use Parse\ParseUser;
    
    session_start();
    //register autoloader
    Twig_Autoloader::register();
    //loader for template files
    $loader = new Twig_Loader_Filesystem('templates');
    //twig instance
    $twig = new Twig_Environment($loader, array(
        'cache' => 'cache',
    ));
    //load template file
    $twig->setCache(false);

    if(isset($_SESSION['user'])){
        $user = $_SESSION['user']; 
        $username = $user->get("username");
        
        if(isset($_GET['logout'])){
            session_unset();
            session_destroy();
            $template = $twig->loadTemplate('home.html');
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $results = $query->find();
            //render a template
            echo $template->render(array('title' => 'iHotel', 'results'=>$results));
        }else if(isset($_GET['add_general'])){
            $template = $twig->loadTemplate('add.html');
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $results = $query->find();
            //render a template
            echo $template->render(array('title' => 'Буудал нэмэх', 'results'=>$results, 'user' => $user, 'nav' => 1));
        }
        else if(isset($_GET['hotels'])){
            $template = $twig->loadTemplate('user_hotels.html');
            $query = new ParseQuery("hotel");
            $query->equalTo("user",$user);
            $hotels = $query->find();
            //render a template
            echo $template->render(array('title' => 'Буудалууд', 'hotels'=>$hotels, 'user' => $user, 'nav' => 4));
        }
        else if(isset($_GET['hotel_add'])){
            $template = $twig->loadTemplate('add_hotel.html');
            $section = $_SESSION['section'];
            $hotel = $_SESSION['hotel'];
            $query = new ParseQuery("rooms");
            $query->equalTo("hotel",$hotel);
            $rooms = $query->find();
            $count = 0;
            foreach ($rooms as $key => $room) {
                $count = $count + $room->get('num_rooms');
            }

            echo $template->render(array('title' => 'Буудал нэмэх', 'hotel'=>$hotel, 'user' => $user, 'nav' => 1, 'section' => $section, 'rooms' => $rooms, 'count' =>$count));
        }
         else if(isset($_GET['settings'])){
            $template = $twig->loadTemplate('user_settings.html');
            //render a template
            echo $template->render(array('title' => 'Нууц үг солих',  'user' => $user, 'nav' => 5));
        }
        else if(isset($_GET['start'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $query->descending("stars");
            $location = $_GET['location'];
            
            $start = $_GET['start'];
            $end = $_GET['end'];

            $date = new DateTime($start);
            $checkin = $date->format('Y-m-d');

            $date = new DateTime($end);
            $checkout = $date->format('Y-m-d');

            $pieces = explode(",", $location);
            $query->equalTo("city",$pieces[0]);
            $results = $query->find();
            $count = $query->count();

            $template = $twig->loadTemplate('list.html');
            //render a template
            echo $template->render(array('title' => 'Хайлт', 'nav' => 1, 'location' => $pieces[0], 'results' =>$results,'start' => $checkin, 'end' => $checkout, 'count' => $count, 'country' => $pieces[2],'user' => $user));
        }
        else if(isset($_GET['search'])){
            $template = $twig->loadTemplate('home.html');
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $query->equalTo("homepage",1);
            $query->equalTo("city","Ulaanbaatar");
            $query->descending("stars");
            $query->limit(25);
            $hotels = $query->find();
            $ub = $query->count();
            //render a template
            echo $template->render(array('title' => 'Search', 'user'=> $user, 'nav' => 1,'ub'=>$ub, 'hotels'=>$hotels));
        }
        else if(isset($_GET['detail'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("objectId",$_GET['detail']);
            $hotel = $query->first();

            $query = new ParseQuery("rooms");
            $query->equalTo("hotel",$hotel);
            $rooms = $query->find();

            $query = new ParseQuery("hotel_images");
            $query->equalTo("hotel",$hotel);
            $query->equalTo("main",1);
            $main = $query->first();

            $query = new ParseQuery("hotel_images");
            $query->equalTo("hotel",$hotel);
            $query->equalTo("main",0);
            $images = $query->find();

            $start = $_GET['depart'];
            $end = $_GET['end'];
            $location = $_GET['location'];
            $country = $_GET['country'];

            $template = $twig->loadTemplate('detail.html');
            //render a template
            echo $template->render(array('title' => 'Дэлгэрэнгүй', 'nav' => 1,  'hotel' =>$hotel,'location' => $location, 'country' =>$country, 'start' => $start, 'end' => $end, 'rooms' => $rooms, 'main' => $main, 'images' =>$images, 'user' =>$user));
        }
        else if(isset($_GET['order'])){
            $query = new ParseQuery("orders");
            $query->equalTo("objectId",$_GET['order']);
            $query->includeKey('hotel');
            $order = $query->first();
            $hotel = $order->get("hotel");
  
            $template = $twig->loadTemplate('order.html');
            //render a template
            echo $template->render(array('title' => 'Захиалга', 'order' => $order, 'hotel' =>$hotel,'user' => $user ));
        }
        else if(isset($_GET['payment'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("objectId",$_GET['hotel']);
            $hotel = $query->first();

            $query = new ParseQuery("rooms");
            $query->equalTo("objectId",$_GET['payment']);
            $room = $query->first();

            $query = new ParseQuery("user_cards");
            $query->equalTo("user",$user);
            $query->equalTo("status",1);
            $cards = $query->find();

            $start = $_GET['depart'];
            $end = $_GET['end'];
            $dStart = new DateTime($start);
            $dEnd  = new DateTime($end);
            $dDiff = $dStart->diff($dEnd);
            //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
            $days = $dDiff->days;
            $totalnum = $dDiff->days * $room->get("night_price");

            $total = number_format(($totalnum), 0);

            $location = $_GET['location'];
            $day_start = date('l', strtotime( $start));
            $day_end = date('l', strtotime( $end));

            $template = $twig->loadTemplate('payment.html');
            //render a template
            echo $template->render(array('title' => 'Төлбөр', 'hotel' =>$hotel, 'location' => $location, 'start' => $start, 'end' => $end, 'room' => $room, 'days' => $dDiff->days, 'total' =>$total, 'totalnum' => $totalnum, 'day_start' => $day_start, 'day_end' => $day_end, 'user' => $user, 'cards' => $cards));
        }
        else if(isset($_GET['city'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $query->equalTo("city",$_GET['city']);
            $query->descending("stars");
            $results = $query->find();
            $date = new DateTime();
            $checkin = $date->format('Y-m-d');
            $date = new DateTime();
            $checkout = $date->format('Y-m-d');
            $count = $query->count();
            $template = $twig->loadTemplate('list.html');
            //render a template
            echo $template->render(array('title' => 'Хайлт', 'nav' => 1, 'location' => $_GET['city'], 'results' =>$results,'start' => $checkin, 'end' => $checkout, 'count' => $count, 'country' => 'iHotel', 'user' =>$user));
        }
        else if(isset($_GET['card'])){
           
            $query = new ParseQuery("user_cards");
            $query->equalTo("status",1);
            $query->equalTo("user",$user);
            $cards = $query->find();

            $template = $twig->loadTemplate('user_cards.html');
            //render a template
            echo $template->render(array('title' => 'Картууд', 'user' => $user, 'cards' => $cards, 'nav' =>3));
        }
        else if(isset($_POST['message'])){
            $template = $twig->loadTemplate('success-payment.html');
            $query = new ParseQuery("orders");
            $query->descending("createdAt");
            $query->equalTo("user",$user);
            $query->includeKey('hotel');
            $orders = $query->find();
            if(isset($_POST['message'])){
                echo $template->render(array('title' => 'iHotel', 'user' => $user, 'nav' => 2, 'orders'=>$orders, 
                    'message'=> $_POST['message'], 'mtype'=>$_POST['mtype'])); 
            }
            else{
                echo $template->render(array('title' => 'iHotel', 'user' => $user, 'nav' => 2, 'orders'=>$orders)); 
            }
        }
        else{
            $template = $twig->loadTemplate('user_dashboard.html');
            $query = new ParseQuery("orders");
            $query->descending("createdAt");
            $query->equalTo("user",$user);
            $query->includeKey('hotel');
            $orders = $query->find();
            echo $template->render(array('title' => 'iHotel', 'user' => $user, 'nav' => 2, 'orders'=>$orders)); 
        }
    }else{
       if(isset($_GET['register'])){
            $template = $twig->loadTemplate('register.html');
            //render a template
            echo $template->render(array('title' => 'Бүртгүүлэх'));
        }
        else if(isset($_GET['start'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $query->descending("stars");
            $location = $_GET['location'];
            
            $start = $_GET['start'];
            $end = $_GET['end'];

            $date = new DateTime($start);
            $checkin = $date->format('Y-m-d');

            $date = new DateTime($end);
            $checkout = $date->format('Y-m-d');

            $pieces = explode(",", $location);
            $query->equalTo("city",$pieces[0]);
            $query->limit(25);
            $results = $query->find();
            $count = $query->count();
            $pages = (int)($count / 25);

            $template = $twig->loadTemplate('list.html');
            //render a template
            echo $template->render(array('title' => 'Хайлт', 'nav' => 1, 'location' => $pieces[0], 'results' =>$results,'start' => $checkin, 'end' => $checkout, 'count' => $count, 'country' => $pieces[2], 'pages' => $pages));
        }
        else if(isset($_GET['detail'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("objectId",$_GET['detail']);
            $hotel = $query->first();

            $query = new ParseQuery("rooms");
            $query->equalTo("hotel",$hotel);
            $rooms = $query->find();

            $query = new ParseQuery("hotel_images");
            $query->equalTo("hotel",$hotel);
            $query->equalTo("main",1);
            $main = $query->first();

            $query = new ParseQuery("hotel_images");
            $query->equalTo("hotel",$hotel);
            $query->equalTo("main",0);
            $images = $query->find();

            $start = $_GET['depart'];
            $end = $_GET['end'];
            $location = $_GET['location'];
            $country = $_GET['country'];

            $template = $twig->loadTemplate('detail.html');
            //render a template
            echo $template->render(array('title' => 'Дэлгэрэнгүй', 'nav' => 1,  'hotel' =>$hotel,'location' => $location, 'country' =>$country, 'start' => $start, 'end' => $end, 'rooms' => $rooms, 'main' => $main, 'images' =>$images));
        }
        else if(isset($_GET['asemdetail'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("objectId",$_GET['asemdetail']);
            $hotel = $query->first();

            $query = new ParseQuery("rooms");
            $query->equalTo("hotel",$hotel);
            $rooms = $query->find();

            $query = new ParseQuery("hotel_images");
            $query->equalTo("hotel",$hotel);
            $query->equalTo("main",1);
            $main = $query->first();

            $query = new ParseQuery("hotel_images");
            $query->equalTo("hotel",$hotel);
            $query->equalTo("main",0);
            $images = $query->find();

            $start = $_GET['depart'];
            $end = $_GET['end'];
            $location = $_GET['location'];
            $country = $_GET['country'];

            $template = $twig->loadTemplate('asem_detail.html');
            //render a template
            echo $template->render(array('title' => 'Дэлгэрэнгүй', 'nav' => 1,  'hotel' =>$hotel,'location' => $location, 'country' =>$country, 'start' => $start, 'end' => $end, 'rooms' => $rooms, 'main' => $main, 'images' =>$images));
        }
        else if(isset($_GET['tour'])){
            $template = $twig->loadTemplate('tour.html');
            echo $template->render(array('title' => 'Аялал'));
        }
        else if(isset($_GET['news'])){
            $template = $twig->loadTemplate('news.html');
            echo $template->render(array('title' => 'Мэдээ'));
        }
        else if(isset($_GET['rent'])){
             $template = $twig->loadTemplate('rent.html');
            echo $template->render(array('title' => 'Мэдээ'));
        }
        else if(isset($_GET['payment'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("objectId",$_GET['hotel']);
            $hotel = $query->first();

            $query = new ParseQuery("rooms");
            $query->equalTo("objectId",$_GET['payment']);
            $room = $query->first();

            $start = $_GET['depart'];
            $end = $_GET['end'];
            $dStart = new DateTime($start);
            $dEnd  = new DateTime($end);
            $dDiff = $dStart->diff($dEnd);
            //echo $dDiff->format('%R'); // use for point out relation: smaller/greater
            $days = $dDiff->days;
            $totalnum = $dDiff->days * $room->get("night_price");

            $total = number_format(($totalnum), 0);

            $location = $_GET['location'];
            $day_start = date('l', strtotime( $start));
            $day_end = date('l', strtotime( $end));

            $template = $twig->loadTemplate('payment.html');
            //render a template
            echo $template->render(array('title' => 'Төлбөр', 'hotel' =>$hotel, 'location' => $location, 'start' => $start, 'end' => $end, 'room' => $room, 'days' => $dDiff->days, 'total' =>$total, 'totalnum' =>$totalnum ,'day_start' => $day_start, 'day_end' => $day_end));
        }
        else if(isset($_GET['order'])){
            $query = new ParseQuery("orders");
            $query->equalTo("objectId",$_GET['order']);
            $query->includeKey('hotel');
            $order = $query->first();
            $hotel = $order->get("hotel");
            $template = $twig->loadTemplate('order.html');
            //render a template
            echo $template->render(array('title' => 'Захиалга', 'order' => $order, 'hotel' =>$hotel ));
        }
        else if(isset($_GET['search'])){
            $template = $twig->loadTemplate('home.html');
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $query->equalTo("homepage",1);
            $query->equalTo("city","Ulaanbaatar");
            $query->descending("stars");
            $query->limit(25);
            $hotels = $query->find();
            $ub = $query->count();
            //render a template
            echo $template->render(array('title' => 'Search', 'nav' => 1,'ub'=>$ub, 'hotels'=>$hotels));
        }
        else if(isset($_GET['city'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $query->equalTo("city",$_GET['city']);
            $query->descending("stars");
            $results = $query->find();
            $date = new DateTime();
            $checkin = $date->format('Y-m-d');
            $date = new DateTime();
            $checkout = $date->format('Y-m-d');
            $count = $query->count();
            $template = $twig->loadTemplate('list.html');
            //render a template
            echo $template->render(array('title' => 'Хайлт', 'nav' => 1, 'location' => $_GET['city'], 'results' =>$results,'start' => $checkin, 'end' => $checkout, 'count' => $count, 'country' => 'iHotel'));
        }
        else if(isset($_GET['asem'])){
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $query->descending("stars");
            
            $query->equalTo("city",'Ulaanbaatar');
            $results = $query->find();
            $count = $query->count();

            $template = $twig->loadTemplate('asem_list.html');
            //render a template
            echo $template->render(array('title' => 'Search', 'nav' => 1, 'results' =>$results));
        }
        else{
            $template = $twig->loadTemplate('home.html');
            $query = new ParseQuery("hotel");
            $query->equalTo("status",1);
            $query->equalTo("homepage",1);
            $query->equalTo("city","Ulaanbaatar");
            $query->descending("stars");
            $query->limit(25);
            $hotels = $query->find();
            $ub = $query->count();
            //render a template
            echo $template->render(array('title' => 'Search', 'nav' => 1,'ub'=>$ub, 'hotels'=>$hotels));
        } 
    }
?>

