import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { loadForm } from '../../Actions/ticketActions';
import { contentSelector, contentLoadingSelector } from '../../Selectors/ticket';

@connect(state => ({
  content: contentSelector(state),
  loading: contentLoadingSelector(state)
}))
export class TicketFormLoaderContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    content: PropTypes.string,
    loading: PropTypes.bool
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(loadForm());
  }

  render() {
    const { loading, content } = this.props;
    if (loading) {
      return (
        <div className="dpdesignportal-content">
          <div className="circle-spinner"><i/></div>
        </div>
      );
    }

    return <div dangerouslySetInnerHTML={{__html: content}} />;
  }
}
