<?php
namespace App\Integrations\StellarCommunityFund;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetRoundProjects extends Request
{
    protected Method $method = Method::GET;
    protected int $page;
    protected int $phaseId;

    public function __construct(int $page, int $phaseId)
    {
        $this->page = $page;
        $this->phaseId = $phaseId;
    }

    public function resolveEndpoint(): string
    {
        return '/suggestion';
    }

    protected function defaultQuery(): array
    {
        return [
            'include' => 'media:filter(deleted:false),author:include(image|displayName|badges),tags:filter(deleted:false),team:include(isTeamLead|displayName):filter(deleted:false),numberOfComments,isLikedByCurrentUser,isRatedByCurrentUser,phase:include(startDate|endDate|primaryStep),comments:include(asCompany|anonym|author|author.displayName|author.image|author.badges|numberOfLikes|numberOfChildComments),comments:filter(deleted:false|internal:false|parentComment:false),comments:limit(3),comments:order(created:desc),suggestionFundingTarget,phaseId,projectId,isEditableByCurrentUser,status,suggestionUserRequirementText,allowSuggestionUserApplication,isSuggestionUserApplicationOpen,suggestionUserApplicationsByCurrentUser,additionalContent:include(formElementOption|formElement):filter(formElement.isHighlighted:true),suggestions.ratings,suggestionAssociations,relativeUrl,suggestionStatusLabel',
            'language' => 'en',
            'limit' => 30,
            'page' => $this->page,
            'order' => 'created:desc,score:desc',
            'filter' => '[{"name":"deleted","modifiers":[{"name":"false","params":[]}]},{"name":"phase","modifiers":[{"name":"is","params":[ '.$this->phaseId.' ]}]}]'
        ];
    }

    protected function defaultHeaders(): array
    {
        return [
            'cookie' => 'ignite=9be4f55ccd48efb6e08375ebf7163d43; PHPSESSID=23b5aaf1e17cc6fe03cea3e824cb8a48',
            'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'
        ];
    }
}
