import PropTypes from 'prop-types';
import React from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import { webhookAction } from '../../Actions/popupActions';

export class PopupWindow extends React.Component {
  static propTypes = {
    data:     PropTypes.object,
    dispatch: PropTypes.func.isRequired
  };

  static getPerson(block) {
    if (block.display === 'detail') {
      return <PersonDetail data={block.data[0]} />;
    }

    return <PersonList data={block.data} />;
  }

  static getTicket(block) {
    if (block.display === 'detail') {
      return <TicketDetail data={block.data[0]} />;
    }

    return <TicketList data={block.data} />;
  }

  static getOrganization(block) {
    if (block.display === 'detail') {
      return <OrganizationDetail data={block.data[0]} />;
    }

    return <OrganizationList data={block.data} />;
  }

  getContent() {
    return (
      <div className="external-event-popup-wrapper">
        <div className="external-event-popup-head">PopupHead</div>
        <div className="external-event-popup-body">{this.getBody()}</div>
        <div className="external-event-popup-footer">
          {this.getActions()}
        </div>
      </div>
    );
  }

  getBody() {
    const { data } = this.props;
    return data.display.map((block) => {
      switch (block.type) {
        case 'html':
          return <div>{block.content}</div>;
        case 'person':
          return PopupWindow.getPerson(block);
        case 'ticket':
          return PopupWindow.getTicket(block);
        case 'organization':
          return PopupWindow.getOrganization(block);
        default:
          return null;
      }
    });
  }

  getActions() {
    const { data } = this.props;
    return data.actions.map((action) => {
      switch (action.type) {
        case 'dismiss':
          return <DismissButton onClick={this.dismissClick} title={action.title} />;
        case 'create_ticket':
          return <CreateTicketButton onClick={this.createTicketClick} title={action.title} />;
        case 'webhook':
          return <WebhookButton onClick={this.webhookClick} {...action} />;
        default:
          return null;
      }
    });
  }

  dismissClick = () => {
    console.log('dismiss');
  };

  createTicketClick = () => {
    console.log('Creating ticket');
  };

  webhookClick = (url, method, data) => {
    this.props.dispatch(webhookAction(url, method, data));
  };

  render() {
    return (<PopUp
      positionMy="left+100 top+200"
      positionAt="left bottom"
      zIndex={99999}
      innerClassName={'external-event-popup'}
      opened
      autoClose={false}
      allowCloseOnClickOut={false}
      content={this.getContent()}
    />);
  }
}

const DismissButton = ({ onClick, title }) => (<button onClick={onClick}>{title}</button>);
DismissButton.propTypes = {
  onClick: PropTypes.func.isRequired,
  title:   PropTypes.string
};
DismissButton.defaultProps = {
  title: 'Dismiss'
};

const CreateTicketButton = ({ onClick, title }) => (<button onClick={onClick}>{title}</button>);
CreateTicketButton.propTypes = {
  onClick: PropTypes.func.isRequired,
  title:   PropTypes.string
};
CreateTicketButton.defaultProps = {
  title: 'Create Ticket'
};

const WebhookButton = ({ onClick, url, method, data, title }) => (
  <button onClick={() => onClick(url, method, data)}>{title}</button>);
WebhookButton.propTypes = {
  onClick: PropTypes.func.isRequired,
  url:     PropTypes.string.isRequired,
  title:   PropTypes.string.isRequired,
  method:  PropTypes.string.isRequired,
  data:    PropTypes.object.isRequired
};


const PersonDetail = ({ data }) => (<div>{data.name}</div>);
PersonDetail.propTypes = {
  data: PropTypes.object.isRequired
};

const TicketDetail = ({ data }) => (<div>{data.title}</div>);
TicketDetail.propTypes = {
  data: PropTypes.object.isRequired
};

const OrganizationDetail = ({ data }) => (<div>{data.title}</div>);
OrganizationDetail.propTypes = {
  data: PropTypes.object.isRequired
};

const PersonList = ({ data }) => (<div>{data.map(person => <div>{person.name}</div>)}</div>);
PersonList.propTypes = {
  data: PropTypes.object.isRequired
};

const TicketList = ({ data }) => (<div>{data.map(ticket => <div>{ticket.title}</div>)}</div>);
TicketList.propTypes = {
  data: PropTypes.object.isRequired
};

const OrganizationList = ({ data }) => (<div>{data.map(organization => <div>{organization.title}</div>)}</div>);
OrganizationList.propTypes = {
  data: PropTypes.object.isRequired
};


export default PopupWindow;
