<?php

namespace App\Controller\Admin;

use App\Entity\Comment;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CommentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Comment::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('user')->autocomplete(),
            AssociationField::new('parent')->autocomplete(),
            TextField::new('comment_type')->formatValue(function($value, $entity){
                if ($value == 'post'){
                    return 'PostId '.$entity->getCommentTypeId();
                }

                if ($value == 'Project'){
                    return 'PostId '.$entity->getCommentTypeId();
                }
            }),
            TextEditorField::new('content'),
            DateTimeField::new('created_at')->onlyOnIndex(),
        ];
    }
}
