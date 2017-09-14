import React, { PropTypes } from 'react';
import difflib from 'difflib';
import diff2html from 'diff2html';
import Modal from 'deskpro-components/lib/Components/Modal';
import agentPhrases from 'DeskPRO/Bundle/AgentBundle/AgentPhrases';

export class ComparisonModal extends React.Component {
  static propTypes = {
    snippet:     PropTypes.object,
    translation: PropTypes.object,
    changes:     PropTypes.array,
    version:     PropTypes.number,
    closeModal:  PropTypes.func,
  };

  constructor(props) {
    super(props);
    let current = {};
    const { version, translation, changes } = this.props;
    const previousKey = changes.length + 1 - version;
    console.log(previousKey);
    if (version === changes.length + 1) {
      current = {
        content: translation.get('content'),
        date:    changes[previousKey].date_created,
      };
    } else {
      current = {
        content: changes[previousKey - 1].content,
        date:    changes[previousKey].date_created,
      };
    }
    const previous = {
      content: changes[previousKey].content,
      date:    this.getPreviousDate(previousKey),
    };

    const render = this.renderDiff(previous, current);
    this.state = {
      render,
      previous,
      current,
    };
  }

  getPreviousDate = (previous) => {
    const { version, changes, snippet } = this.props;
    if (version > 2) {
      return changes[previous + 1].date_created;
    }
    return snippet.get('date_created');
  };

  renderDiff(previous, current) {
    const { snippet } = this.props;
    let diff = difflib.unifiedDiff(
      previous.content.replace(/<br ?\/?>/g, '\n').split('\n'),
      current.content.replace(/<br ?\/?>/g, '\n').split('\n'), {
        fromfile:     'Previous',
        tofile:       'Current',
        fromfiledate: previous.date,
        tofiledate:   current.date,
        lineterm:     ''
      }).join('\n');
    diff = `${`diff --git a/${snippet.get('title').replace(/ /g, '_')} b/${snippet.get('title').replace(/ /g, '_')}\n` +
      'index f67b6fb..2773e7a 100644\n'}${
      diff}`;
    return diff2html.Diff2Html.getPrettyHtml(diff, { outputFormat: 'side-by-side' });
  }

  render() {
    const { closeModal } = this.props;
    return (
      <div id="comparison_modal">
        <Modal
          title={agentPhrases.get('agent.general.comparison')}
          closeModal={closeModal}
        >
          <div dangerouslySetInnerHTML={{ __html: this.state.render }} />
        </Modal>
      </div>
    );
  }
}
