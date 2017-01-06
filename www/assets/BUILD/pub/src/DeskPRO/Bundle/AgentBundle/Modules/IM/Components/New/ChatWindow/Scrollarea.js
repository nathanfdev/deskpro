import ScrollArea from 'react-scrollbar';

class Scrollarea extends ScrollArea {

  constructor(props) {
    super(props);
    this.autoscroll = true;
  }

  componentWillReceiveProps() {
    if (this.state.topPosition === this.state.realHeight - this.state.containerHeight) {
      this.autoscroll = true;
    }
  }

  componentDidUpdate() {
    this.setSizesToState();
    if (this.autoscroll && this.state.topPosition !== this.state.realHeight - this.state.containerHeight) {
      this.scrollBottom();
      this.autoscroll = false;
    }
  }
}

export default Scrollarea;
