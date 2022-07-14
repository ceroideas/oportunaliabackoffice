import { Component, OnInit } from '@angular/core';
import Swal from 'sweetalert2'

@Component({
  selector: 'app-ui-dialogs',
  templateUrl: './ui-dialogs.component.html',
  styleUrls: ['./ui-dialogs.component.scss']
})
export class UiDialogsComponent implements OnInit {

  constructor() { }

  ngOnInit(): void {
  }
  sweettalert1() {
    Swal.fire("Here's a message!")
  }
  sweettalert2() {
    Swal.fire({
      title: "Here's a message!",
      text: "It's pretty, isn't it?",
    })
  }
  sweettalert3() {
    Swal.fire({
      title: "Good job!",
      text: "You clicked the button!",
      icon: "success"
    })
  }
  sweettalert4() {
    Swal.fire
      ({
        html: "<h2 style='font-weight:bold'>HTML Title!</h2>A custom <a style='color:red'>html message.</a>",
      })
  }
  sweettalert5() {
    Swal.fire
      ({
        html: "<img style='width:80px;height:80px' src='../../../../../assets/images/sm/avatar2.jpg'><h2 class='mt-3'>Sweet!</h2><p>Here's a custom image.</p>",
      })
  }
  sweettalert6() {
    Swal.fire
      ({
        title: "Auto close alert!",
        text: "I will close in 2 seconds.",
        showConfirmButton: false,
        timer: 2000
      })
  }
  sweettalert7() {
    Swal.fire({
      title: 'Are you sure?',
      text: 'You will not be able to recover this imaginary file!',
      icon: 'warning',
      showCancelButton: true,
      cancelButtonText: 'Cancel',
      confirmButtonColor: "rgb(220, 53, 69)",
      confirmButtonText: 'Yes, delete it!',

    }).then((result) => {
      if (result.value) {
        Swal.fire(
          'Deleted!',
          'Your imaginary file has been deleted.',
          'success'
        )

      } else if (result.dismiss === Swal.DismissReason.cancel) {
        Swal.fire(
          'Cancelled',
          'Your imaginary file is safe :)',
          'error'
        )
      }
    })
  }

}
