import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import Modal from 'react-modal';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { deleteComment, editComment } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';
import { CommentCard } from './CommentCard';
import { idsSelector } from '../../../../Selectors/list';
import { selectedSelector } from '../../../../../Application/Selectors/massActions';

@connect(
  state => ({
    ids:      idsSelector(state),
    comments: collectionSelectorFactory('FeedbackComment', 'feedback')(state),
    selected: selectedSelector(state),
    people:   collectionSelectorFactory('Person', 'feedback')(state),
    feedback: collectionSelectorFactory('Feedback', 'feedback')(state)
  })
)
export class CommentCardsContainer extends Component {
  static propTypes = {
    dispatch:       PropTypes.func.isRequired,
    ids:            PropTypes.array.isRequired,
    comments:       PropTypes.object.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    people:         PropTypes.object.isRequired,
    feedback:       PropTypes.object.isRequired,
    selected:       PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = { approveIsOpen: false, deleteIsOpen: false };

    const appElement = document.getElementById('deskpro_app_window');
    Modal.setAppElement(appElement);
  }

  openModal = (type, id, event) => {
    event.preventDefault();
    this.setState({ id });
    let params = { deleteIsOpen: true };
    if (type === 'approve') {
      params = { approveIsOpen: true };
    }
    this.setState(params);
  };

  closeModal = event => {
    event.preventDefault();
    this.setState({ approveIsOpen: false, deleteIsOpen: false });
  };

  approveComment = event => {
    event.preventDefault();
    const newValues = {
      status:      constants.STATUS_VISIBLE,
      is_reviewed: true
    };
    this.props.dispatch(editComment(newValues, this.state.id));
  };

  handleDeleteComment = (event) => {
    event.preventDefault();
    this.props.dispatch(deleteComment(this.state.id));
  };

  renderComment(id) {
    const { dispatch, comments, selected, toggleSelected, people, feedback } = this.props;
    const element = comments.get(id);
    return (
      <CommentCard
        key={id}
        comment={element}
        dispatch={dispatch}
        feedback={feedback.get(element.get('feedback'))}
        selected={selected.includes(id)}
        toggleSelected={toggleSelected}
        openModal={this.openModal}
        author={people.get(element.get('person'))}
      />
    );
  }

  render() {
    const { ids } = this.props;

    // Inline styles before the new design will be implemented
    const customStyles = {
      overlay: {
        zIndex: 1005
      },
      content: {
        position:    'absolute',
        border:      '2px solid blue',
        top:         '25%',
        left:        '25%',
        right:       'auto',
        bottom:      'auto',
        width:       '50%',
        marginRight: '25%'
      }
    };

    return (
      <div>
        <Modal
          isOpen={this.state.approveIsOpen}
          onRequestClose={this.closeModal}
          style={customStyles}
        >
          <h1>Approve comment?</h1>
          <a href="#" className="dpw--panel-button" onClick={this.closeModal}>Cancel</a>
          <a href="#" className="dpw--panel-button" onClick={this.approveComment}>Approve</a>
        </Modal>
        <Modal
          isOpen={this.state.deleteIsOpen}
          onRequestClose={this.closeModal}
          style={customStyles}
        >
          <h1>Delete comment?</h1>
          <a href="#" className="dpw--panel-button" onClick={this.closeModal}>Cancel</a>
          <a href="#" className="dpw--panel-button" onClick={this.handleDeleteComment}>Delete</a>
        </Modal>
        {ids.map(id => this.renderComment(id))}
      </div>
    );
  }
}
