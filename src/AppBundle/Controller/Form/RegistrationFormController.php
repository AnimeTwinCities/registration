<?php
/**
 * Copyright (c) 2018. Anime Twin Cities, Inc.
 *
 * This project, including all of the files and their contents, is licensed under the terms of MIT License
 *
 * See the LICENSE file in the root of this project for details.
 */

namespace AppBundle\Controller\Form;

use AppBundle\Entity\Event;
use AppBundle\Service\TCPDF\RegistrationPDF;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RegistrationFormController extends Controller
{
    /**
     * @Route("/form/registration", name="form_registration")
     *
     * @return StreamedResponse
     */
    public function getRegistrationForm()
    {
        $event = $this->getDoctrine()->getRepository(Event::class)->getSelectedEvent();

        /** @var RegistrationPDF $pdf */
        $pdf = $this->get("white_october.tcpdf")->create();
        $pdf->setEvent($event);
        $pdf->setSubTitle('Registration Form');

        $pdf->setFontSubsetting(false);
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // margin above header
        $pdf->SetHeaderMargin(0);
        $pdf->SetLeftMargin(13);
        $pdf->SetRightMargin(5);
        $pdf->SetAutoPageBreak(true, 45);

        //tcpdf set some language-dependent strings -- ugg why no default?
        global $l;
        $pdf->setLanguageArray($l);

        // set font
        $pdf->SetFont('helvetica', '', 10);

        $pdf->AddPage();
        $pdf->Ln(10);
        $pdf->setFormDefaultProp(array('lineWidth'=>1, 'borderStyle'=>'solid', 'fillColor'=>array(255, 224, 194), 'strokeColor'=>array(254, 127, 0)));

        $pdf->SetFont('Bauhaus LT Medium', 'B', 14);
        $pdf->Cell(155, 16, 'Child  ($15) Age 6-12');
        $pdf->CheckBox('reg_standard', 16);
        $pdf->cell('15');
        $pdf->Cell(170, 16, 'Sponsor ($150)');
        $pdf->CheckBox('reg_sponsor', 16);
        $pdf->cell('40');
        $pdf->Cell(160, 16, 'Sponsor Only Breakfast');
        $pdf->Ln(16);

        $pdf->Cell(155, 16, 'Minor ($40) Age 13-17');
        $pdf->CheckBox('reg_standard_late', 16);
        $pdf->cell('15');
        $pdf->Cell(170, 16, 'Community Sponsor ($250)');
        $pdf->CheckBox('reg_commsponsor', 16);
        $pdf->cell('40');
        $pdf->Cell(95, 16, 'Add-on (+$30)');
        $pdf->CheckBox('breakfast', 16);
        $pdf->Ln(16);

        $pdf->Cell(155, 16, 'Adult  ($50) Until 1/31');
        $pdf->CheckBox('reg_standard_late', 16);
        $pdf->Ln(16);

        $pdf->Cell(155, 16, 'Adult  ($60) 2/1+');
        $pdf->CheckBox('reg_standard_late', 16);
        $pdf->cell('15');
        $pdf->Cell(170, 16, 'Adult ($75) At-Door');
        $pdf->CheckBox('reg_standard_door', 16);
        $pdf->Ln(16);

        $field1_width = 570;
        $pdf->SetFont('Bauhaus LT Medium', 'B', 20);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Membership Information');
        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $pdf->Ln(30);

        $pdf->SetFont('helvetica', 'B', 10);
        $field1_width = 200;
        $field2_width = 200;
        $field3_width = 150;
        $pdf->TextField('lastname', $field1_width, 16);
        $pdf->cell('10');
        $pdf->TextField('firstname', $field2_width, 16);
        $pdf->cell('10');
        $pdf->TextField('middlename', $field3_width, 16);
        $pdf->Ln(16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Last Name');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, 'First Name');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field3_width, $y);
        $pdf->Cell($field3_width, 16, 'Middle Name');
        $pdf->Ln(20);

        $pdf->SetFont('helvetica', 'B', 10);
        $field1_width = 300;
        $field2_width = 260;
        $pdf->TextField('address1', $field1_width, 16);
        $pdf->cell('10');
        $pdf->TextField('address2', $field2_width, 16);
        $pdf->Ln(16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Postal Mailing Address');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, 'Address 2 (if needed)');
        $pdf->Ln(20);

        $pdf->SetFont('helvetica', 'B', 10);
        $field1_width = 400;
        $field2_width = 50;
        $field3_width = 100;
        $pdf->TextField('city', $field1_width, 16);
        $pdf->cell('10');
        $pdf->TextField('state', $field2_width, 16);
        $pdf->cell('10');
        $pdf->TextField('zip', $field3_width, 16);
        $pdf->Ln(16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'City');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, 'State');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field3_width, $y);
        $pdf->Cell($field3_width, 16, 'Zip');
        $pdf->Ln(20);

        $pdf->SetFont('helvetica', 'B', 10);
        $field1_width = 260;
        $field2_width = 300;
        $pdf->TextField('phone', $field1_width, 16);
        $pdf->cell('10');
        $pdf->TextField('email', $field2_width, 16);
        $pdf->Ln(16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Phone');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, 'E-mail');
        $pdf->Ln(20);

        $pdf->SetFont('helvetica', 'B', 10);
        $field0_width = 140;
        $field1_width = 30;
        $field2_width = 30;
        $field3_width = 60;
        $pdf->TextField('badgename', $field0_width, 16);
        $pdf->cell('10');
        $pdf->TextField('birth_month', $field1_width, 16);
        $pdf->cell('10', '16', '/');
        $pdf->TextField('birth_day', $field2_width, 16);
        $pdf->cell('10', '16', '/');
        $pdf->TextField('birth_year', $field3_width, 16);
        $pdf->Ln(16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field0_width, $y);
        $pdf->Cell($field0_width, 16, 'Badge Name');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'DOB: (MM/DD/YYYY)');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, '');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field3_width, $y);
        $pdf->Cell($field3_width, 16, '');

        $pdf->Ln(20);

        $pdf->Ln(10);
        $field1_width = 260;
        $field2_width = 300;
        $pdf->SetFont('Bauhaus LT Medium', 'B', 20);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width+$field2_width+10, $y);
        $pdf->Cell($field1_width, 16, 'Emergency Contact');
        $pdf->Cell('10');
        //$pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, 'Accessibility Information');
        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $pdf->Ln(30);

        $pdf->SetFont('helvetica', 'B', 10);
        $field1_width = 260;
        $field2_width = 300;
        $pdf->TextField('emergency_contact', $field1_width, 16);
        $pdf->cell('10');
        $pdf->TextField('disability_type_1', $field2_width, 16);
        $pdf->Ln(16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Contact Name');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, 'Do you have an accessibility need?');
        $pdf->Ln(20);

        $pdf->SetFont('helvetica', 'B', 10);
        $field1_width = 260;
        $field2_width = 300;
        $pdf->TextField('phone', $field1_width, 16);
        $pdf->cell('10');
        $pdf->TextField('disability_type_2', $field2_width, 16);
        $pdf->Ln(16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Contact Phone');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->TextField('disability_type_3', $field2_width, 18);
        $pdf->Ln(20);

        $pdf->SetFont('helvetica', 'B', 10);
        $field1_width = 260;
        $field2_width = 300;
        $pdf->TextField('relation', $field1_width, 16);
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y+18, $pdf->GetX()+$field2_width, $y+18);
        $pdf->TextField('disability_type_4', $field2_width, 16);
        $pdf->Ln(16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Attendee Relation to Contact');
        $pdf->cell('10');
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, 'How we can we assist?');
        $pdf->Ln(20);

        //$pdf->SetY($old_y);
        $pdf->Ln(10);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width+$field2_width+10, $y);
        $pdf->Ln(15);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell(50, 16, 'Mens:', 0, 0, 'R');
        $pdf->Cell(20, 16, 'S', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_S', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('10');
        $pdf->Cell(20, 16, 'M', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_M', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('10');
        $pdf->Cell(20, 16, 'L', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_L', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('10');
        $pdf->Cell(20, 16, 'XL', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_XL', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('10');
        $pdf->Cell(20, 16, 'XLT', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_XLT', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('20');
        $pdf->Cell(20, 16, '2XL', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_2XL', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('20');
        $pdf->Cell(20, 16, '2XLT', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_2XLT', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('25');
        $pdf->Cell(20, 16, '3XL', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_3XL', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('20');
        $pdf->Cell(20, 16, '3XLT', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_3XLT', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('25');
        $pdf->Cell(20, 16, '4XL', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('mens_4XL', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Ln(18);
        $pdf->Cell(50, 16, 'Womens:', 0, 0, 'R');
        $pdf->Cell(20, 16, 'S', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('womens_S', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('10');
        $pdf->Cell(20, 16, 'M', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('womens_M', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('10');
        $pdf->Cell(20, 16, 'L', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('womens_L', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('10');
        $pdf->Cell(20, 16, 'XL', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('womens_XL', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('44');
        //$pdf->Cell(20, 16, 'XLT', 0, 0, 'R');
        //$pdf->TextField('womens_XLT', 14, 16);
        //$pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell('20');
        $pdf->Cell(20, 16, '2XL', 0, 0, 'R');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->TextField('womens_2XL', 14, 16);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Ln(18);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        $field1_width = 570;
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'T-Shirt? $15/shirt (Sponsor/Community Sponsor receive 1 free shirt)');
        $pdf->SetFont('helvetica', 'B', 8);
        $pdf->ln();
        $pdf->cell('20');
        $pdf->Cell('300', 16, 'Shirts must be paid for with your pre-registration. Please note the additional amount in the total below.');
        $pdf->Ln(20);

        $field1_width = 210;
        $field2_width = 180;
        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $y = $pdf->GetY();
        //$pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Would you like to Volunteer?');
        $pdf->CheckBox('volunteer', 16);
        $pdf->cell('30');
        //$pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field2_width, $y);
        $pdf->Cell($field2_width, 16, 'Receive our Newsletter?');
        $pdf->CheckBox('newsletter', 16);
        $pdf->Ln(25);

        $y = $pdf->GetY();
        $pdf->SetFont('Bauhaus LT Medium', '', 12);
        $pdf->Cell(170, 16, 'Please make checks payable to: ');
        $pdf->SetFont('Bauhaus', 'B', 12);
        $pdf->Cell(100, 16, 'Anime Twin Cities');
        $pdf->Ln(18);
        $pdf->SetFont('Bauhaus LT Medium', '', 12);
        $pdf->Cell(400, 16, 'Mail your check along with this completed form to:');
        $pdf->Ln(18);
        $pdf->SetFont('Bauhaus', 'B', 14);
        $pdf->Cell('10');
        $pdf->Cell(400, 16, 'Anime Twin Cities');
        $pdf->Ln(14);
        $pdf->Cell('10');
        $pdf->Cell(400, 16, 'P.O. Box 48309');
        $pdf->Ln(14);
        $pdf->Cell('10');
        $pdf->Cell(400, 16, 'Coon Rapids, MN 55448');
        $pdf->Ln(18);
        $pdf->SetFont('Bauhaus LT Medium', 'B', 12);
        $pdf->Cell(400, 16, 'Please do not send cash.');


        $pdf->SetFont('helvetica', 'B', 10);
        $field1_width = 200;
        $pdf->SetXY('350', $y+50);
        $pdf->TextField('total_paid', $field1_width, 16);

        $pdf->SetFont('Bauhaus LT Medium', 'B', 16);
        $pdf->SetXY('350', $y+66);
        $y = $pdf->GetY();
        $pdf->Line($pdf->GetX(), $y, $pdf->GetX()+$field1_width, $y);
        $pdf->Cell($field1_width, 16, 'Total Amount Paid');

        return new StreamedResponse(function () use ($pdf) {
            $pdf->Output('ADRegistration.pdf', 'I');
        });
    }
}
