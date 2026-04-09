<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\SiteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CalendarController extends AbstractController
{
    private array $monthNames = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
    ];

    public function index(EventRepository $eventRepository, SiteRepository $siteRepository): Response
    {
        $site = $siteRepository->findOneBy(['isActive' => true]);
        
        $now = new \DateTimeImmutable();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('n');

        $events = $eventRepository->findByMonth($year, $month);

        $calendarWeeks = $this->buildCalendarWeeks($year, $month, $events);

        return $this->render('calendar/index.html.twig', [
            'events' => $events,
            'year' => $year,
            'month' => $month,
            'monthName' => $this->monthNames[$month],
            'calendarWeeks' => $calendarWeeks,
            'site' => $site,
        ]);
    }

    public function show(string $slug, EventRepository $eventRepository): Response
    {
        $event = $eventRepository->findOneBySlug($slug);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        return $this->render('calendar/show.html.twig', [
            'event' => $event,
            'site' => $event->getSite(),
        ]);
    }

    public function day(int $year, int $month, int $day, EventRepository $eventRepository, SiteRepository $siteRepository): Response
    {
        if ($month < 1 || $month > 12) {
            $month = 1;
        }
        if ($day < 1 || $day > 31) {
            $day = 1;
        }

        $site = $siteRepository->findOneBy(['isActive' => true]);
        
        $events = $site 
            ? $eventRepository->findByDay($year, $month, $day, $site->getId())
            : $eventRepository->findByDay($year, $month, $day);

        // Build hourly slots (24 hours)
        $hourlySlots = [];
        for ($hour = 0; $hour < 24; $hour++) {
            $hourStart = sprintf('%04d-%02d-%02d %02d:00:00', $year, $month, $day, $hour);
            $hourEnd = sprintf('%04d-%02d-%02d %02d:59:59', $year, $month, $day, $hour);
            
            $hourEvents = [];
            foreach ($events as $event) {
                $eventHour = (int) $event->getStartAt()->format('H');
                if ($eventHour === $hour) {
                    $hourEvents[] = $event;
                }
            }
            
            $hourlySlots[] = [
                'hour' => $hour,
                'hourFormatted' => sprintf('%02d:00', $hour),
                'events' => $hourEvents,
            ];
        }

        $currentDate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, $month, $day));
        $isToday = $currentDate->format('Y-m-d') === (new \DateTimeImmutable())->format('Y-m-d');

        return $this->render('calendar/day.html.twig', [
            'year' => $year,
            'month' => $month,
            'day' => $day,
            'monthName' => $this->monthNames[$month],
            'hourlySlots' => $hourlySlots,
            'events' => $events,
            'site' => $site,
            'isToday' => $isToday,
        ]);
    }

    public function month(int $year, int $month, EventRepository $eventRepository, SiteRepository $siteRepository): Response
    {
        if ($month < 1 || $month > 12) {
            $month = 1;
        }

        $site = $siteRepository->findOneBy(['isActive' => true]);
        
        $events = $site 
            ? $eventRepository->findByMonth($year, $month, $site->getId())
            : $eventRepository->findByMonth($year, $month);

        $calendarWeeks = $this->buildCalendarWeeks($year, $month, $events);

        return $this->render('calendar/index.html.twig', [
            'events' => $events,
            'year' => $year,
            'month' => $month,
            'monthName' => $this->monthNames[$month],
            'calendarWeeks' => $calendarWeeks,
            'site' => $site,
        ]);
    }

    private function buildCalendarWeeks(int $year, int $month, array $events): array
    {
        $firstDayOfMonth = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $daysInMonth = (int) $firstDayOfMonth->format('t');
        $firstDayOfWeek = (int) $firstDayOfMonth->format('w');

        $eventDays = [];
        foreach ($events as $event) {
            $eventStartDay = (int) $event->getStartAt()->format('j');
            if (!isset($eventDays[$eventStartDay])) {
                $eventDays[$eventStartDay] = [];
            }
            $eventDays[$eventStartDay][] = $event;
        }

        $weeks = [];
        $currentDay = 1;
        $nextMonthDay = 1;

        for ($week = 0; $week < 6; $week++) {
            $weekData = [];
            
            for ($dayOfWeek = 0; $dayOfWeek < 7; $dayOfWeek++) {
                $cellDay = ($week * 7) + $dayOfWeek + 1 - $firstDayOfWeek;
                
                if ($cellDay >= 1 && $cellDay <= $daysInMonth) {
                    $weekData[] = [
                        'day' => $cellDay,
                        'isCurrentMonth' => true,
                        'isToday' => $this->isToday($year, $month, $cellDay),
                        'events' => $eventDays[$cellDay] ?? [],
                    ];
                } elseif ($cellDay < 1) {
                    $prevMonth = $month == 1 ? 12 : $month - 1;
                    $prevYear = $month == 1 ? $year - 1 : $year;
                    $prevMonthDays = (int) (new \DateTimeImmutable(sprintf('%04d-%02d-01', $prevYear, $prevMonth)))->format('t');
                    $weekData[] = [
                        'day' => $prevMonthDays + $cellDay,
                        'isCurrentMonth' => false,
                        'isToday' => false,
                        'events' => [],
                    ];
                } else {
                    $weekData[] = [
                        'day' => $nextMonthDay++,
                        'isCurrentMonth' => false,
                        'isToday' => false,
                        'events' => [],
                    ];
                }
            }
            
            $weeks[] = $weekData;
            
            if ($currentDay >= $daysInMonth && $nextMonthDay > 1) {
                break;
            }
        }

        return $weeks;
    }

    private function isToday(int $year, int $month, int $day): bool
    {
        $today = new \DateTimeImmutable();
        return $today->format('Y') == $year 
            && $today->format('n') == $month 
            && $today->format('j') == $day;
    }
}
