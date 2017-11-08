import ScrollArea from '@deskpro/react-scrollbar';

class Scrollarea extends ScrollArea {

  constructor(props) {
    super(props);
    this.autoscroll = true;
    this.messageToScroll = false;
    this.edgeIncrease = true;
  }

  componentWillReceiveProps(props) {
    if (this.state.topPosition === this.state.realHeight - this.state.containerHeight) {
      this.autoscroll = true;
    }
    if (this.edgeIncrease && this.state.realHeight > this.state.containerHeight) {
      this.autoscroll = true;
      this.edgeIncrease = false;
    }
    this.messageToScroll = props.messageToScroll;
  }

  componentDidUpdate() {
    this.setSizesToState();
    if (this.autoscroll && this.state.topPosition !== this.state.realHeight - this.state.containerHeight) {
      this.scrollBottom();
      this.autoscroll = false;
    }
    if (this.messageToScroll && !this.messageToScroll.isSearchResult() && this.messageToScroll.getNode()) {
      this.props.onScrollToMessage();
      const messageNodeRect = this.messageToScroll.getNode().getBoundingClientRect();
      const chatContextRect = this.wrapper.getBoundingClientRect();
      const deltaY = messageNodeRect.top - chatContextRect.top;
      this.messageToScroll = false;
      this.scrollYTo(this.state.topPosition + deltaY);
    }
  }

  setStateFromEvent(newState, eventType) {
    if (this.props.onScroll) {
      this.props.onScroll({ ...newState, component: this });
    }
    this.setState({ ...newState, eventType });
  }
}

export default Scrollarea;
